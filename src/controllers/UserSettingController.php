<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\UserSetting;
use App\Helper\ResponseHelper;
use App\Controllers\UserController;
use App\Controllers\SettingsController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

class UserSettingController
{
    private UserController $userController;
    private SettingsController $settingsController;

    public function __construct(UserController $userController, SettingsController $settingsController)
    {
        $this->userController = $userController;
        $this->settingsController = $settingsController;
    }

    /**
     * Get all user settings
     */
    public function index(Request $request, Response $response): Response
    {
        try {
            $settings = UserSetting::all();
            return ResponseHelper::success($response, 'User settings fetched successfully', $settings->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch user settings', 500, $e->getMessage());
        }
    }

    /**
     * Get single user setting entry
     */
    public function show(Request $request, Response $response, array $args): Response
    {
        try {
            $setting = UserSetting::find($args['id']);
            if (!$setting) {
                return ResponseHelper::error($response, 'User setting not found', 404);
            }
            return ResponseHelper::success($response, 'User setting fetched successfully', $setting->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to fetch user setting', 500, $e->getMessage());
        }
    }

    /**
     * Create user setting entry
     */
    public function create(Request $request, Response $response): Response
    {
        try {
            $data = (array)($request->getParsedBody() ?? []);

            if (empty($data['userId']) || empty($data['category'])) {
                return ResponseHelper::error($response, 'userId and category are required', 400);
            }

            $userId = (int)$data['userId'];

            // Verify user exists using UserController
            $userResponse = $this->userController->show($request, new \Slim\Psr7\Response(), ['id' => (string)$userId]);
            if ($userResponse->getStatusCode() === 404) {
                return ResponseHelper::error($response, "Invalid User ID {$userId}. User not found.", 400);
            }

            $setting = UserSetting::create($data);

            return ResponseHelper::success($response, 'User setting created successfully', $setting->toArray(), 201);
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to create user setting', 500, $e->getMessage());
        }
    }

    /**
     * Update user setting entry
     */
    public function update(Request $request, Response $response, array $args): Response
    {
        try {
            $setting = UserSetting::find($args['id']);
            if (!$setting) {
                return ResponseHelper::error($response, 'User setting not found', 404);
            }

            $data = (array)($request->getParsedBody() ?? []);
            $setting->update($data);

            return ResponseHelper::success($response, 'User setting updated successfully', $setting->toArray());
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to update user setting', 500, $e->getMessage());
        }
    }

    /**
     * Delete user setting entry
     */
    public function delete(Request $request, Response $response, array $args): Response
    {
        try {
            $setting = UserSetting::find($args['id']);
            if (!$setting) {
                return ResponseHelper::error($response, 'User setting not found', 404);
            }

            $setting->delete();
            return ResponseHelper::success($response, 'User setting deleted successfully');
        } catch (Exception $e) {
            return ResponseHelper::error($response, 'Failed to delete user setting', 500, $e->getMessage());
        }
    }
}
