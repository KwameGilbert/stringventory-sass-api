<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\PushSubscription;
use App\Models\AuditLog;
use App\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

class PushSubscriptionController
{
    /**
     * Get all push subscriptions
     */
    public function index(Request $request, Response $response): Response
    {
        try {
            $pushSubscriptions = PushSubscription::with(['user'])->get();
            return ResponseHelper::success($response, 'Push subscriptions fetched successfully', $pushSubscriptions->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch push subscriptions', 500, $e->getMessage());
        }
    }

    /**
     * Get single push subscription
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $pushSubscription = PushSubscription::with(['user'])->find($args['id']);
            if (!$pushSubscription) {
                return ResponseHelper::error($response, 'Push subscription not found', 404);
            }
            return ResponseHelper::success($response, 'Push subscription fetched successfully', $pushSubscription->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch push subscription', 500, $e->getMessage());
        }
    }

    /**
     * Create push subscription
     */
    public function create(Request $request, Response $response): Response
    {
        try {
            $data = (array)($request->getParsedBody() ?? []);

            if (empty($data['userId']) || empty($data['endpoint']) || empty($data['p256dhKey']) || empty($data['authKey'])) {
                return ResponseHelper::error($response, 'userId, endpoint, p256dhKey, and authKey are required', 400);
            }

            $pushSubscription = PushSubscription::create($data);

            $user = $request->getAttribute('user');
            AuditLog::log($request, $user ? $user->businessId : null, $user ? $user->id : null, 'push_subscription_created', [
                'pushSubscriptionId' => $pushSubscription->id,
                'userId' => $pushSubscription->userId,
            ]);

            return ResponseHelper::success($response, 'Push subscription created successfully', $pushSubscription->toArray(), 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create push subscription', 500, $e->getMessage());
        }
    }

    /**
     * Update push subscription
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $pushSubscription = PushSubscription::find($args['id']);
            if (!$pushSubscription) {
                return ResponseHelper::error($response, 'Push subscription not found', 404);
            }

            $data = (array)($request->getParsedBody() ?? []);
            $pushSubscription->update($data);

            $user = $request->getAttribute('user');
            AuditLog::log($request, $user ? $user->businessId : null, $user ? $user->id : null, 'push_subscription_updated', [
                'pushSubscriptionId' => $pushSubscription->id,
            ]);

            return ResponseHelper::success($response, 'Push subscription updated successfully', $pushSubscription->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update push subscription', 500, $e->getMessage());
        }
    }

    /**
     * Delete push subscription
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $pushSubscription = PushSubscription::find($args['id']);
            if (!$pushSubscription) {
                return ResponseHelper::error($response, 'Push subscription not found', 404);
            }

            $pushSubscriptionId = $pushSubscription->id;
            $userId = $pushSubscription->userId;
            $pushSubscription->delete();

            $user = $request->getAttribute('user');
            AuditLog::log($request, $user ? $user->businessId : null, $user ? $user->id : null, 'push_subscription_deleted', [
                'pushSubscriptionId' => $pushSubscriptionId,
                'userId' => $userId,
            ]);

            return ResponseHelper::success($response, 'Push subscription deleted successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete push subscription', 500, $e->getMessage());
        }
    }
}
