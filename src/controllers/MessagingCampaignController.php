<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\MessagingCampaign;
use App\Models\AuditLog;
use App\Helper\ResponseHelper;
use App\Controllers\MessagingTemplateController;
use App\Services\MessagingService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

class MessagingCampaignController
{
    private MessagingTemplateController $messagingTemplateController;
    private MessagingService $messagingService;

    public function __construct(
        MessagingTemplateController $messagingTemplateController,
        MessagingService $messagingService
    ) {
        $this->messagingTemplateController = $messagingTemplateController;
        $this->messagingService = $messagingService;
    }

    /**
     * Get all messaging campaigns
     */
    public function index(Request $request, Response $response): Response
    {
        try {
            $campaigns = MessagingCampaign::with(['template', 'creator'])->get();
            return ResponseHelper::success($response, 'Messaging campaigns fetched successfully', $campaigns->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch messaging campaigns', 500, $e->getMessage());
        }
    }

    /**
     * Get single messaging campaign
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $campaign = MessagingCampaign::with(['template', 'creator', 'recipients'])->find($args['id']);
            if (!$campaign) {
                return ResponseHelper::error($response, 'Messaging campaign not found', 404);
            }
            return ResponseHelper::success($response, 'Messaging campaign fetched successfully', $campaign->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch messaging campaign', 500, $e->getMessage());
        }
    }

    /**
     * Create/Send messaging campaign using MessagingService
     */
    public function create(Request $request, Response $response): Response
    {
        try {
            $data = (array)($request->getParsedBody() ?? []);

            if (empty($data['recipientIds']) || !is_array($data['recipientIds'])) {
                return ResponseHelper::error($response, 'recipientIds is required and must be an array.', 400);
            }

            if (empty($data['body']) && empty($data['templateId'])) {
                return ResponseHelper::error($response, 'Either body or templateId is required.', 400);
            }

            // Verify template exists using MessagingTemplateController if templateId is provided
            if (!empty($data['templateId'])) {
                $templateId = (int)$data['templateId'];
                $templateResponse = $this->messagingTemplateController->show($request, new \Slim\Psr7\Response(), ['id' => (string)$templateId]);
                if ($templateResponse->getStatusCode() === 404) {
                    return ResponseHelper::error($response, "Invalid Template ID {$templateId}. Template not found.", 400);
                }
            }

            $user = $request->getAttribute('user');
            $createdBy = $user ? (int)$user->id : null;

            // Delegate send logic to MessagingService
            $result = $this->messagingService->sendBulk($data, $createdBy);

            $campaignId = $result['campaignId'] ?? null;
            if ($campaignId) {
                AuditLog::log($request, $user ? $user->businessId : null, $createdBy, 'messaging_campaign_created', [
                    'campaignId' => $campaignId,
                    'subject' => $data['subject'] ?? null,
                ]);
            }

            return ResponseHelper::success($response, 'Messaging campaign launched successfully', $result, 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create/launch messaging campaign', 500, $e->getMessage());
        }
    }

    /**
     * Update messaging campaign
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $campaign = MessagingCampaign::find($args['id']);
            if (!$campaign) {
                return ResponseHelper::error($response, 'Messaging campaign not found', 404);
            }

            $data = (array)($request->getParsedBody() ?? []);
            $campaign->update($data);

            $user = $request->getAttribute('user');
            AuditLog::log($request, $campaign->businessId, $user ? $user->id : null, 'messaging_campaign_updated', [
                'campaignId' => $campaign->id,
                'status' => $campaign->status,
            ]);

            return ResponseHelper::success($response, 'Messaging campaign updated successfully', $campaign->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update messaging campaign', 500, $e->getMessage());
        }
    }

    /**
     * Delete messaging campaign
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $campaign = MessagingCampaign::find($args['id']);
            if (!$campaign) {
                return ResponseHelper::error($response, 'Messaging campaign not found', 404);
            }

            $campaignId = $campaign->id;
            $businessId = $campaign->businessId;
            $campaign->delete();

            $user = $request->getAttribute('user');
            AuditLog::log($request, $businessId, $user ? $user->id : null, 'messaging_campaign_deleted', [
                'campaignId' => $campaignId,
            ]);

            return ResponseHelper::success($response, 'Messaging campaign deleted successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete messaging campaign', 500, $e->getMessage());
        }
    }
}
