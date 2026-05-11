<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\RefreshToken;
use App\Models\AuditLog;
use App\Helper\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

class RefreshTokenController
{
    /**
     * Get all refresh tokens
     */
    public function index(Request $request, Response $response): Response
    {
        try {
            $refreshTokens = RefreshToken::with(['user'])->get();
            return ResponseHelper::success($response, 'Refresh tokens fetched successfully', $refreshTokens->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch refresh tokens', 500, $e->getMessage());
        }
    }

    /**
     * Get single refresh token
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $refreshToken = RefreshToken::with(['user'])->find($args['id']);
            if (!$refreshToken) {
                return ResponseHelper::error($response, 'Refresh token not found', 404);
            }
            return ResponseHelper::success($response, 'Refresh token fetched successfully', $refreshToken->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch refresh token', 500, $e->getMessage());
        }
    }

    /**
     * Create refresh token
     */
    public function create(Request $request, Response $response): Response
    {
        try {
            $data = (array)($request->getParsedBody() ?? []);

            if (empty($data['userId']) || empty($data['tokenHash']) || empty($data['expiresAt'])) {
                return ResponseHelper::error($response, 'userId, tokenHash, and expiresAt are required', 400);
            }

            $refreshToken = RefreshToken::create($data);

            return ResponseHelper::success($response, 'Refresh token created successfully', $refreshToken->toArray(), 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create refresh token', 500, $e->getMessage());
        }
    }

    /**
     * Update refresh token
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $refreshToken = RefreshToken::find($args['id']);
            if (!$refreshToken) {
                return ResponseHelper::error($response, 'Refresh token not found', 404);
            }

            $data = (array)($request->getParsedBody() ?? []);
            $refreshToken->update($data);

            return ResponseHelper::success($response, 'Refresh token updated successfully', $refreshToken->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update refresh token', 500, $e->getMessage());
        }
    }

    /**
     * Delete/Revoke refresh token
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $refreshToken = RefreshToken::find($args['id']);
            if (!$refreshToken) {
                return ResponseHelper::error($response, 'Refresh token not found', 404);
            }

            $refreshToken->update([
                'revoked' => true,
                'revokedAt' => date('Y-m-d H:i:s')
            ]);

            $user = $request->getAttribute('user');
            AuditLog::log($request, $user ? $user->businessId : null, $user ? $user->id : null, 'refresh_token_revoked', [
                'tokenId' => $refreshToken->id,
                'userId' => $refreshToken->userId,
            ]);

            return ResponseHelper::success($response, 'Refresh token revoked successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to revoke refresh token', 500, $e->getMessage());
        }
    }
}
