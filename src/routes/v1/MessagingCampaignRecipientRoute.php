<?php

declare(strict_types=1);

use App\Controllers\MessagingCampaignRecipientController;
use Slim\App;

return function (App $app): void {
    $messagingCampaignRecipientController = $app->getContainer()->get(MessagingCampaignRecipientController::class);
    
    $app->group('/v1/messaging-campaign-recipients', function ($group) use ($messagingCampaignRecipientController) {
        $group->get('', [$messagingCampaignRecipientController, 'index']);
        $group->get('/{id}', [$messagingCampaignRecipientController, 'show']);
        $group->post('', [$messagingCampaignRecipientController, 'create']);
        $group->put('/{id}', [$messagingCampaignRecipientController, 'update']);
        $group->delete('/{id}', [$messagingCampaignRecipientController, 'delete']);
    });
};
