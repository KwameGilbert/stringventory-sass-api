<?php

declare(strict_types=1);

use App\Controllers\BusinessController;
use Slim\App;

return function (App $app): void {
    $businessController = $app->getContainer()->get(BusinessController::class);
    
    $app->group('/v1/businesses', function ($group) use ($businessController) {
        $group->get('', [$businessController, 'index']);
        $group->get('/{id}', [$businessController, 'show']);
        $group->post('', [$businessController, 'create']);
        $group->put('/{id}', [$businessController, 'update']);
        $group->delete('/{id}', [$businessController, 'delete']);
    });
};
