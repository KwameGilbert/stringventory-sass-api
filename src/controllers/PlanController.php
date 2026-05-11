<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Plan;
use App\Models\AuditLog;
use App\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

class PlanController
{
    /**
     * Get all plans
     */
    public function index(Request $request, Response $response): Response
    {
        try {
            $plans = Plan::all();
            return ResponseHelper::success($response, 'Plans fetched successfully', $plans->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch plans', 500, $e->getMessage());
        }
    }

    /**
     * Get single plan
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $plan = Plan::find($args['id']);
            if (!$plan) {
                return ResponseHelper::error($response, 'Plan not found', 404);
            }
            return ResponseHelper::success($response, 'Plan fetched successfully', $plan->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch plan', 500, $e->getMessage());
        }
    }

    /**
     * Create plan
     */
    public function create(Request $request, Response $response): Response
    {
        try {
            $data = (array)($request->getParsedBody() ?? []);

            if (empty($data['name']) || empty($data['description'])) {
                return ResponseHelper::error($response, 'Plan name and description are required', 400);
            }

            $plan = Plan::create($data);

            $user = $request->getAttribute('user');
            AuditLog::log($request, $user ? $user->businessId : null, $user ? $user->id : null, 'plan_created', [
                'planId' => $plan->id,
                'name' => $plan->name,
            ]);

            return ResponseHelper::success($response, 'Plan created successfully', $plan->toArray(), 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create plan', 500, $e->getMessage());
        }
    }

    /**
     * Update plan
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $plan = Plan::find($args['id']);
            if (!$plan) {
                return ResponseHelper::error($response, 'Plan not found', 404);
            }

            $data = (array)($request->getParsedBody() ?? []);
            $plan->update($data);

            $user = $request->getAttribute('user');
            AuditLog::log($request, $user ? $user->businessId : null, $user ? $user->id : null, 'plan_updated', [
                'planId' => $plan->id,
                'name' => $plan->name,
            ]);

            return ResponseHelper::success($response, 'Plan updated successfully', $plan->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update plan', 500, $e->getMessage());
        }
    }

    /**
     * Delete plan
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $plan = Plan::find($args['id']);
            if (!$plan) {
                return ResponseHelper::error($response, 'Plan not found', 404);
            }

            $planId = $plan->id;
            $planName = $plan->name;
            $plan->delete();

            $user = $request->getAttribute('user');
            AuditLog::log($request, $user ? $user->businessId : null, $user ? $user->id : null, 'plan_deleted', [
                'planId' => $planId,
                'name' => $planName,
            ]);

            return ResponseHelper::success($response, 'Plan deleted successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete plan', 500, $e->getMessage());
        }
    }
}
