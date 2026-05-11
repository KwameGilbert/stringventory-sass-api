<?php

declare(strict_types=1);

use App\Controllers\PurchaseItemController;
use Slim\App;

return function (App $app): void {
    $purchaseItemController = $app->getContainer()->get(PurchaseItemController::class);
    
    $app->group('/v1/purchase-items', function ($group) use ($purchaseItemController) {
        $group->get('', [$purchaseItemController, 'index']);
        $group->get('/{id}', [$purchaseItemController, 'show']);
        $group->post('', [$purchaseItemController, 'create']);
        $group->put('/{id}', [$purchaseItemController, 'update']);
        $group->delete('/{id}', [$purchaseItemController, 'delete']);
    });
};
