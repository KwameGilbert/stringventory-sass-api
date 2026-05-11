<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Business;
use App\Models\AuditLog;
use App\Helper\ResponseHelper;
use App\Services\NotificationService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

class BusinessController
{
    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get all businesses
     */
    public function index(Request $request, Response $response): Response
    {
        try {
            $businesses = Business::withCount(['users'])->get();
            return ResponseHelper::success($response, 'Businesses fetched successfully', $businesses->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch businesses', 500, $e->getMessage());
        }
    }

    /**
     * Get single business
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $business = Business::with(['users', 'subscription'])->find($args['id']);
            if (!$business) {
                return ResponseHelper::error($response, 'Business not found', 404);
            }
            return ResponseHelper::success($response, 'Business fetched successfully', $business->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch business', 500, $e->getMessage());
        }
    }

    /**
     * Create business
     */
    public function create(Request $request, Response $response): Response
    {
        try {
            $data = (array)($request->getParsedBody() ?? []);

            if (empty($data['name']) || empty($data['email'])) {
                return ResponseHelper::error($response, 'Business name and email are required', 400);
            }

            $business = Business::create($data);

            $user = $request->getAttribute('user');
            AuditLog::log($request, $business->id, $user ? $user->id : null, 'business_created', [
                'businessId' => $business->id,
                'name' => $business->name,
            ]);

            // Notify admins
            $this->notificationService->notifyAdmins(
                'business_registration',
                'New Business Registered',
                "Business '{$business->name}' has been successfully registered.",
                ['businessId' => $business->id]
            );

            return ResponseHelper::success($response, 'Business created successfully', $business->toArray(), 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create business', 500, $e->getMessage());
        }
    }

    /**
     * Update business
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $business = Business::find($args['id']);
            if (!$business) {
                return ResponseHelper::error($response, 'Business not found', 404);
            }

            $data = (array)($request->getParsedBody() ?? []);
            $business->update($data);

            $user = $request->getAttribute('user');
            AuditLog::log($request, $business->id, $user ? $user->id : null, 'business_updated', [
                'businessId' => $business->id,
                'name' => $business->name,
            ]);

            return ResponseHelper::success($response, 'Business updated successfully', $business->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update business', 500, $e->getMessage());
        }
    }

    /**
     * Delete business
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $business = Business::find($args['id']);
            if (!$business) {
                return ResponseHelper::error($response, 'Business not found', 404);
            }

            $businessId = $business->id;
            $businessName = $business->name;
            $business->delete();

            $user = $request->getAttribute('user');
            AuditLog::log($request, null, $user ? $user->id : null, 'business_deleted', [
                'businessId' => $businessId,
                'name' => $businessName,
            ]);

            // Notify admins
            $this->notificationService->notifyAdmins(
                'business_deletion',
                'Business Terminated',
                "Business '{$businessName}' has been terminated/deleted.",
                ['businessId' => $businessId]
            );

            return ResponseHelper::success($response, 'Business deleted successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete business', 500, $e->getMessage());
        }
    }
}
