<?php

declare(strict_types=1);

use App\Controllers\PaymentMethodController;
use Slim\App;

return function (App $app): void {
    $paymentMethodController = $app->getContainer()->get(PaymentMethodController::class);
    
    $app->group('/v1/payment-methods', function ($group) use ($paymentMethodController) {
        $group->get('', [$paymentMethodController, 'index']);
        $group->get('/{id}', [$paymentMethodController, 'show']);
        $group->post('', [$paymentMethodController, 'create']);
        $group->put('/{id}', [$paymentMethodController, 'update']);
        $group->delete('/{id}', [$paymentMethodController, 'delete']);
    });
};
