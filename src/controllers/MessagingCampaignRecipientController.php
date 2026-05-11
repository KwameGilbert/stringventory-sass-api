<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\MessagingCampaignRecipient;
use App\Helper\ResponseHelper;
use App\Controllers\CustomerController;
use App\Controllers\MessagingCampaignController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

class MessagingCampaignRecipientController
{
    private CustomerController $customerController;
    private MessagingCampaignController $messagingCampaignController;

    public function __construct(CustomerController $customerController, MessagingCampaignController $messagingCampaignController)
    {
        $this->customerController = $customerController;
        $this->messagingCampaignController = $messagingCampaignController;
    }

    /**
     * Get all messaging campaign recipients
     */
    public function index(Request $request, Response $response): Response
    {
        try {
            $recipients = MessagingCampaignRecipient::with(['campaign', 'customer'])->get();
            return ResponseHelper::success($response, 'Messaging campaign recipients fetched successfully', $recipients->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch messaging campaign recipients', 500, $e->getMessage());
        }
    }

    /**
     * Get single messaging campaign recipient
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $recipient = MessagingCampaignRecipient::with(['campaign', 'customer'])->find($args['id']);
            if (!$recipient) {
                return ResponseHelper::error($response, 'Messaging campaign recipient not found', 404);
            }
            return ResponseHelper::success($response, 'Messaging campaign recipient fetched successfully', $recipient->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch messaging campaign recipient', 500, $e->getMessage());
        }
    }

    /**
     * Create messaging campaign recipient
     */
    public function create(Request $request, Response $response): Response
    {
        try {
            $data = (array)($request->getParsedBody() ?? []);

            if (empty($data['businessId']) || empty($data['campaignId']) || empty($data['customerId']) || empty($data['channel'])) {
                return ResponseHelper::error($response, 'businessId, campaignId, customerId, and channel are required', 400);
            }

            $campaignId = (int)$data['campaignId'];
            $customerId = (int)$data['customerId'];

            // Verify campaign exists
            $campaignResponse = $this->messagingCampaignController->show($request, new \Slim\Psr7\Response(), ['id' => (string)$campaignId]);
            if ($campaignResponse->getStatusCode() === 404) {
                return ResponseHelper::error($response, "Invalid Campaign ID {$campaignId}. Campaign not found.", 400);
            }

            // Verify customer exists
            $customerResponse = $this->customerController->show($request, new \Slim\Psr7\Response(), ['id' => (string)$customerId]);
            if ($customerResponse->getStatusCode() === 404) {
                return ResponseHelper::error($response, "Invalid Customer ID {$customerId}. Customer not found.", 400);
            }

            $recipient = MessagingCampaignRecipient::create($data);

            return ResponseHelper::success($response, 'Messaging campaign recipient created successfully', $recipient->toArray(), 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create messaging campaign recipient', 500, $e->getMessage());
        }
    }

    /**
     * Update messaging campaign recipient
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $recipient = MessagingCampaignRecipient::find($args['id']);
            if (!$recipient) {
                return ResponseHelper::error($response, 'Messaging campaign recipient not found', 404);
            }

            $data = (array)($request->getParsedBody() ?? []);
            $recipient->update($data);

            return ResponseHelper::success($response, 'Messaging campaign recipient updated successfully', $recipient->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update messaging campaign recipient', 500, $e->getMessage());
        }
    }

    /**
     * Delete messaging campaign recipient
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $recipient = MessagingCampaignRecipient::find($args['id']);
            if (!$recipient) {
                return ResponseHelper::error($response, 'Messaging campaign recipient not found', 404);
            }

            $recipient->delete();
            return ResponseHelper::success($response, 'Messaging campaign recipient deleted successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete messaging campaign recipient', 500, $e->getMessage());
        }
    }
}
