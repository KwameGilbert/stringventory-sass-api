<?php

declare(strict_types=1);

use App\Controllers\PlanController;
use Slim\App;

return function (App $app): void {
    $planController = $app->getContainer()->get(PlanController::class);
    
    $app->group('/v1/plans', function ($group) use ($planController) {
        $group->get('', [$planController, 'index']);
        $group->get('/{id}', [$planController, 'show']);
        $group->post('', [$planController, 'create']);
        $group->put('/{id}', [$planController, 'update']);
        $group->delete('/{id}', [$planController, 'delete']);
    });
};
