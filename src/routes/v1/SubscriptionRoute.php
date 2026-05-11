<?php

declare(strict_types=1);

use App\Controllers\SubscriptionController;
use Slim\App;

return function (App $app): void {
    $subscriptionController = $app->getContainer()->get(SubscriptionController::class);
    
    $app->group('/v1/subscriptions', function ($group) use ($subscriptionController) {
        $group->get('', [$subscriptionController, 'index']);
        $group->get('/{id}', [$subscriptionController, 'show']);
        $group->post('', [$subscriptionController, 'create']);
        $group->put('/{id}', [$subscriptionController, 'update']);
        $group->delete('/{id}', [$subscriptionController, 'delete']);
    });
};
