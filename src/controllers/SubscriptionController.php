<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Subscription;
use App\Models\AuditLog;
use App\Helper\ResponseHelper;
use App\Services\NotificationService;
use App\Controllers\BusinessController;
use App\Controllers\PlanController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

class SubscriptionController
{
    private BusinessController $businessController;
    private PlanController $planController;
    private NotificationService $notificationService;

    public function __construct(
        BusinessController $businessController,
        PlanController $planController,
        NotificationService $notificationService
    ) {
        $this->businessController = $businessController;
        $this->planController = $planController;
        $this->notificationService = $notificationService;
    }

    /**
     * Get all subscriptions
     */
    public function index(Request $request, Response $response): Response
    {
        try {
            $subscriptions = Subscription::with(['business', 'plan'])->get();
            return ResponseHelper::success($response, 'Subscriptions fetched successfully', $subscriptions->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch subscriptions', 500, $e->getMessage());
        }
    }

    /**
     * Get single subscription
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $subscription = Subscription::with(['business', 'plan'])->find($args['id']);
            if (!$subscription) {
                return ResponseHelper::error($response, 'Subscription not found', 404);
            }
            return ResponseHelper::success($response, 'Subscription fetched successfully', $subscription->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch subscription', 500, $e->getMessage());
        }
    }

    /**
     * Create subscription
     */
    public function create(Request $request, Response $response): Response
    {
        try {
            $data = (array)($request->getParsedBody() ?? []);

            if (empty($data['businessId']) || empty($data['planId'])) {
                return ResponseHelper::error($response, 'Business ID and Plan ID are required', 400);
            }

            // Verify Business & Plan exist using the related controllers
            $businessId = (int)$data['businessId'];
            $planId = (int)$data['planId'];

            $businessResponse = $this->businessController->show($request, new \Slim\Psr7\Response(), ['id' => (string)$businessId]);
            if ($businessResponse->getStatusCode() === 404) {
                return ResponseHelper::error($response, "Invalid Business ID {$businessId}. Business not found.", 400);
            }

            $planResponse = $this->planController->show($request, new \Slim\Psr7\Response(), ['id' => (string)$planId]);
            if ($planResponse->getStatusCode() === 404) {
                return ResponseHelper::error($response, "Invalid Plan ID {$planId}. Plan not found.", 400);
            }

            $subscription = Subscription::create($data);

            $user = $request->getAttribute('user');
            AuditLog::log($request, (int)$data['businessId'], $user ? $user->id : null, 'subscription_created', [
                'subscriptionId' => $subscription->id,
                'businessId' => $subscription->businessId,
                'planId' => $subscription->planId,
            ]);

            // Notify admins about a new subscription
            $this->notificationService->notifyAdmins(
                'subscription_created',
                'New Subscription Activated',
                "Business ID {$subscription->businessId} subscribed to Plan ID {$subscription->planId}.",
                ['subscriptionId' => $subscription->id]
            );

            return ResponseHelper::success($response, 'Subscription created successfully', $subscription->toArray(), 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create subscription', 500, $e->getMessage());
        }
    }

    /**
     * Update subscription
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $subscription = Subscription::find($args['id']);
            if (!$subscription) {
                return ResponseHelper::error($response, 'Subscription not found', 404);
            }

            $data = (array)($request->getParsedBody() ?? []);
            $subscription->update($data);

            $user = $request->getAttribute('user');
            AuditLog::log($request, $subscription->businessId, $user ? $user->id : null, 'subscription_updated', [
                'subscriptionId' => $subscription->id,
                'status' => $subscription->status,
            ]);

            return ResponseHelper::success($response, 'Subscription updated successfully', $subscription->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update subscription', 500, $e->getMessage());
        }
    }

    /**
     * Delete subscription
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $subscription = Subscription::find($args['id']);
            if (!$subscription) {
                return ResponseHelper::error($response, 'Subscription not found', 404);
            }

            $subscriptionId = $subscription->id;
            $businessId = $subscription->businessId;
            $subscription->delete();

            $user = $request->getAttribute('user');
            AuditLog::log($request, $businessId, $user ? $user->id : null, 'subscription_deleted', [
                'subscriptionId' => $subscriptionId,
            ]);

            return ResponseHelper::success($response, 'Subscription deleted successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete subscription', 500, $e->getMessage());
        }
    }
}
