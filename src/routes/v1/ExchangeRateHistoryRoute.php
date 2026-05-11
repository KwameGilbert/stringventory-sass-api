<?php

declare(strict_types=1);

use App\Controllers\ExchangeRateHistoryController;
use Slim\App;

return function (App $app): void {
    $exchangeRateHistoryController = $app->getContainer()->get(ExchangeRateHistoryController::class);
    
    $app->group('/v1/exchange-rate-histories', function ($group) use ($exchangeRateHistoryController) {
        $group->get('', [$exchangeRateHistoryController, 'index']);
        $group->get('/{id}', [$exchangeRateHistoryController, 'show']);
        $group->post('', [$exchangeRateHistoryController, 'create']);
        $group->put('/{id}', [$exchangeRateHistoryController, 'update']);
        $group->delete('/{id}', [$exchangeRateHistoryController, 'delete']);
    });
};
