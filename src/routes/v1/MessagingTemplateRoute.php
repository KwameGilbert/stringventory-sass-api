<?php

declare(strict_types=1);

use App\Controllers\MessagingTemplateController;
use Slim\App;

return function (App $app): void {
    $messagingTemplateController = $app->getContainer()->get(MessagingTemplateController::class);
    
    $app->group('/v1/messaging-templates', function ($group) use ($messagingTemplateController) {
        $group->get('', [$messagingTemplateController, 'index']);
        $group->get('/{id}', [$messagingTemplateController, 'show']);
        $group->post('', [$messagingTemplateController, 'create']);
        $group->put('/{id}', [$messagingTemplateController, 'update']);
        $group->delete('/{id}', [$messagingTemplateController, 'delete']);
    });
};
