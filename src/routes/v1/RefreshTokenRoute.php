<?php

declare(strict_types=1);

use App\Controllers\RefreshTokenController;
use Slim\App;

return function (App $app): void {
    $refreshTokenController = $app->getContainer()->get(RefreshTokenController::class);
    
    $app->group('/v1/refresh-tokens', function ($group) use ($refreshTokenController) {
        $group->get('', [$refreshTokenController, 'index']);
        $group->get('/{id}', [$refreshTokenController, 'show']);
        $group->post('', [$refreshTokenController, 'create']);
        $group->put('/{id}', [$refreshTokenController, 'update']);
        $group->delete('/{id}', [$refreshTokenController, 'delete']);
    });
};
