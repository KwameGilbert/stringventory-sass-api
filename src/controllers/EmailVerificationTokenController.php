<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\EmailVerificationToken;
use App\Helper\ResponseHelper;
use App\Controllers\UserController;
use App\Services\VerificationService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

class EmailVerificationTokenController
{
    private UserController $userController;
    private VerificationService $verificationService;

    public function __construct(UserController $userController, VerificationService $verificationService)
    {
        $this->userController = $userController;
        $this->verificationService = $verificationService;
    }

    /**
     * Get all email verification tokens
     */
    public function index(Request $request, Response $response): Response
    {
        try {
            $tokens = EmailVerificationToken::orderBy('createdAt', 'desc')->get();
            return ResponseHelper::success($response, 'Email verification tokens fetched successfully', $tokens->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch email verification tokens', 500, $e->getMessage());
        }
    }

    /**
     * Get single email verification token
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $token = EmailVerificationToken::find($args['id']);
            if (!$token) {
                return ResponseHelper::error($response, 'Email verification token not found', 404);
            }
            return ResponseHelper::success($response, 'Email verification token fetched successfully', $token->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch email verification token', 500, $e->getMessage());
        }
    }

    /**
     * Create/Send email verification token
     */
    public function create(Request $request, Response $response): Response
    {
        try {
            $data = (array)($request->getParsedBody() ?? []);

            if (empty($data['userId'])) {
                return ResponseHelper::error($response, 'userId is required', 400);
            }

            $userId = (int)$data['userId'];

            // Fetch user using UserController
            $userResponse = $this->userController->show($request, new \Slim\Psr7\Response(), ['id' => (string)$userId]);
            if ($userResponse->getStatusCode() === 404) {
                return ResponseHelper::error($response, "Invalid User ID {$userId}. User not found.", 400);
            }

            // Obtain user model reference
            $user = \App\Models\User::find($userId);
            if (!$user) {
                return ResponseHelper::error($response, 'User not found.', 404);
            }

            // Trigger verification email sending using VerificationService
            $sent = $this->verificationService->sendVerificationEmail($user);
            if (!$sent) {
                return ResponseHelper::error($response, 'Failed to send verification email.', 500);
            }

            return ResponseHelper::success($response, 'Verification email sent successfully.', [], 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create email verification token', 500, $e->getMessage());
        }
    }

    /**
     * Update email verification token
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $token = EmailVerificationToken::find($args['id']);
            if (!$token) {
                return ResponseHelper::error($response, 'Email verification token not found', 404);
            }

            $data = (array)($request->getParsedBody() ?? []);
            $token->update($data);

            return ResponseHelper::success($response, 'Email verification token updated successfully', $token->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update email verification token', 500, $e->getMessage());
        }
    }

    /**
     * Delete email verification token
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $token = EmailVerificationToken::find($args['id']);
            if (!$token) {
                return ResponseHelper::error($response, 'Email verification token not found', 404);
            }

            $token->delete();
            return ResponseHelper::success($response, 'Email verification token deleted successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete email verification token', 500, $e->getMessage());
        }
    }
}
