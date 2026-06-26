<?php

declare(strict_types=1);

use App\Controllers\BusinessController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use Slim\App;

return function (App $app): void {
    $businessController = $app->getContainer()->get(BusinessController::class);
    $authMiddleware = $app->getContainer()->get(AuthMiddleware::class);
    
    $superadminRole = ['superadmin'];

    $app->group('/v1/businesses', function ($group) use ($businessController) {
        $group->get('', [$businessController, 'index']);
        $group->get('/{id}', [$businessController, 'show']);
        $group->post('', [$businessController, 'create']);
        $group->put('/{id}', [$businessController, 'update']);
        $group->delete('/{id}', [$businessController, 'delete']);
        $group->post('/{id}/suspend', [$businessController, 'suspend']);
        $group->post('/{id}/reactivate', [$businessController, 'reactivate']);
    })->add(new RoleMiddleware($superadminRole))->add($authMiddleware);
};
