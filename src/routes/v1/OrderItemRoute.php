<?php

declare(strict_types=1);

use App\Controllers\OrderItemController;
use Slim\App;

return function (App $app): void {
    $orderItemController = $app->getContainer()->get(OrderItemController::class);
    
    $app->group('/v1/order-items', function ($group) use ($orderItemController) {
        $group->get('', [$orderItemController, 'index']);
        $group->get('/{id}', [$orderItemController, 'show']);
        $group->post('', [$orderItemController, 'create']);
        $group->put('/{id}', [$orderItemController, 'update']);
        $group->delete('/{id}', [$orderItemController, 'delete']);
    });
};
