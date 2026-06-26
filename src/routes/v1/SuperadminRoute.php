<?php

declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\SettingsController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use Slim\App;

return function (App $app): void {
    $adminController = $app->getContainer()->get(AdminController::class);
    $settingsController = $app->getContainer()->get(SettingsController::class);
    $authMiddleware = $app->getContainer()->get(AuthMiddleware::class);
    
    // The superadmin role string from the frontend expects "superadmin" or "super_admin". 
    // We will use "superadmin".
    $superadminRole = ['superadmin'];

    $app->group('/v1/superadmin', function ($group) use ($adminController, $settingsController) {
        $group->get('/analytics/platform', [$adminController, 'getPlatformAnalytics']);
        $group->get('/settings', [$settingsController, 'getSuperadminSettings']);
        $group->put('/settings', [$settingsController, 'updateSuperadminSettings']);
    })->add(new RoleMiddleware($superadminRole))->add($authMiddleware);
};
