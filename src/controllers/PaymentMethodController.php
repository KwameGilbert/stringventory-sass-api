<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\PaymentMethod;
use App\Models\AuditLog;
use App\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

class PaymentMethodController
{
    /**
     * Get all payment methods
     */
    public function index(Request $request, Response $response): Response
    {
        try {
            $paymentMethods = PaymentMethod::all();
            return ResponseHelper::success($response, 'Payment methods fetched successfully', $paymentMethods->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch payment methods', 500, $e->getMessage());
        }
    }

    /**
     * Get single payment method
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $paymentMethod = PaymentMethod::find($args['id']);
            if (!$paymentMethod) {
                return ResponseHelper::error($response, 'Payment method not found', 404);
            }
            return ResponseHelper::success($response, 'Payment method fetched successfully', $paymentMethod->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch payment method', 500, $e->getMessage());
        }
    }

    /**
     * Create payment method
     */
    public function create(Request $request, Response $response): Response
    {
        try {
            $data = (array)($request->getParsedBody() ?? []);

            if (empty($data['businessId']) || empty($data['methodCode']) || empty($data['name'])) {
                return ResponseHelper::error($response, 'Business ID, method code, and name are required', 400);
            }

            $paymentMethod = PaymentMethod::create($data);

            $user = $request->getAttribute('user');
            AuditLog::log($request, $paymentMethod->businessId, $user ? $user->id : null, 'payment_method_created', [
                'paymentMethodId' => $paymentMethod->id,
                'name' => $paymentMethod->name,
            ]);

            return ResponseHelper::success($response, 'Payment method created successfully', $paymentMethod->toArray(), 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create payment method', 500, $e->getMessage());
        }
    }

    /**
     * Update payment method
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $paymentMethod = PaymentMethod::find($args['id']);
            if (!$paymentMethod) {
                return ResponseHelper::error($response, 'Payment method not found', 404);
            }

            $data = (array)($request->getParsedBody() ?? []);
            $paymentMethod->update($data);

            $user = $request->getAttribute('user');
            AuditLog::log($request, $paymentMethod->businessId, $user ? $user->id : null, 'payment_method_updated', [
                'paymentMethodId' => $paymentMethod->id,
                'name' => $paymentMethod->name,
            ]);

            return ResponseHelper::success($response, 'Payment method updated successfully', $paymentMethod->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update payment method', 500, $e->getMessage());
        }
    }

    /**
     * Delete payment method
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $paymentMethod = PaymentMethod::find($args['id']);
            if (!$paymentMethod) {
                return ResponseHelper::error($response, 'Payment method not found', 404);
            }

            $paymentMethodId = $paymentMethod->id;
            $businessId = $paymentMethod->businessId;
            $paymentMethodName = $paymentMethod->name;
            $paymentMethod->delete();

            $user = $request->getAttribute('user');
            AuditLog::log($request, $businessId, $user ? $user->id : null, 'payment_method_deleted', [
                'paymentMethodId' => $paymentMethodId,
                'name' => $paymentMethodName,
            ]);

            return ResponseHelper::success($response, 'Payment method deleted successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete payment method', 500, $e->getMessage());
        }
    }
}
