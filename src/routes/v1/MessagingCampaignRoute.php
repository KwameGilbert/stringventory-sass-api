<?php

declare(strict_types=1);

use App\Controllers\MessagingCampaignController;
use Slim\App;

return function (App $app): void {
    $messagingCampaignController = $app->getContainer()->get(MessagingCampaignController::class);
    
    $app->group('/v1/messaging-campaigns', function ($group) use ($messagingCampaignController) {
        $group->get('', [$messagingCampaignController, 'index']);
        $group->get('/{id}', [$messagingCampaignController, 'show']);
        $group->post('', [$messagingCampaignController, 'create']);
        $group->put('/{id}', [$messagingCampaignController, 'update']);
        $group->delete('/{id}', [$messagingCampaignController, 'delete']);
    });
};
