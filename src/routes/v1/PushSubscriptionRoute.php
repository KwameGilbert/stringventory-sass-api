<?php

declare(strict_types=1);

use App\Controllers\PushSubscriptionController;
use Slim\App;

return function (App $app): void {
    $pushSubscriptionController = $app->getContainer()->get(PushSubscriptionController::class);
    
    $app->group('/v1/push-subscriptions', function ($group) use ($pushSubscriptionController) {
        $group->get('', [$pushSubscriptionController, 'index']);
        $group->get('/{id}', [$pushSubscriptionController, 'show']);
        $group->post('', [$pushSubscriptionController, 'create']);
        $group->put('/{id}', [$pushSubscriptionController, 'update']);
        $group->delete('/{id}', [$pushSubscriptionController, 'delete']);
    });
};
