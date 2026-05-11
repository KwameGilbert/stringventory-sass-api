<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\MessagingTemplate;
use App\Models\AuditLog;
use App\Helper\ResponseHelper;
use App\Services\MessagingService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

class MessagingTemplateController
{
    private MessagingService $messagingService;

    public function __construct(MessagingService $messagingService)
    {
        $this->messagingService = $messagingService;
    }

    /**
     * Get all messaging templates
     */
    public function index(Request $request, Response $response): Response
    {
        try {
            $templates = MessagingTemplate::all();
            return ResponseHelper::success($response, 'Messaging templates fetched successfully', $templates->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch messaging templates', 500, $e->getMessage());
        }
    }

    /**
     * Get single messaging template
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $template = MessagingTemplate::find($args['id']);
            if (!$template) {
                return ResponseHelper::error($response, 'Messaging template not found', 404);
            }
            return ResponseHelper::success($response, 'Messaging template fetched successfully', $template->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch messaging template', 500, $e->getMessage());
        }
    }

    /**
     * Create messaging template using MessagingService
     */
    public function create(Request $request, Response $response): Response
    {
        try {
            $payload = (array)($request->getParsedBody() ?? []);
            
            // Delegate creation and validation to MessagingService
            $template = $this->messagingService->createTemplate($payload);

            $user = $request->getAttribute('user');
            AuditLog::log($request, $template['businessId'] ?? null, $user ? $user->id : null, 'messaging_template_created', [
                'templateId' => $template['id'],
                'name' => $template['name'],
            ]);

            return ResponseHelper::success($response, 'Messaging template created successfully', $template, 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create messaging template', 422, $e->getMessage());
        }
    }

    /**
     * Update messaging template using MessagingService
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $templateId = (int)($args['id'] ?? 0);
            if ($templateId <= 0) {
                return ResponseHelper::error($response, 'Invalid template ID', 422);
            }

            $payload = (array)($request->getParsedBody() ?? []);
            
            // Delegate update and validation to MessagingService
            $template = $this->messagingService->updateTemplate($templateId, $payload);

            if (!$template) {
                return ResponseHelper::error($response, 'Messaging template not found', 404);
            }

            $user = $request->getAttribute('user');
            AuditLog::log($request, $template['businessId'] ?? null, $user ? $user->id : null, 'messaging_template_updated', [
                'templateId' => $template['id'],
                'name' => $template['name'],
            ]);

            return ResponseHelper::success($response, 'Messaging template updated successfully', $template);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update messaging template', 422, $e->getMessage());
        }
    }

    /**
     * Delete messaging template using MessagingService
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $templateId = (int)($args['id'] ?? 0);
            if ($templateId <= 0) {
                return ResponseHelper::error($response, 'Invalid template ID', 422);
            }

            $deleted = $this->messagingService->deleteTemplate($templateId);
            if (!$deleted) {
                return ResponseHelper::error($response, 'Messaging template not found', 404);
            }

            $user = $request->getAttribute('user');
            AuditLog::log($request, $user ? $user->businessId : null, $user ? $user->id : null, 'messaging_template_deleted', [
                'templateId' => $templateId,
            ]);

            return ResponseHelper::success($response, 'Messaging template deleted successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete messaging template', 500, $e->getMessage());
        }
    }
}
