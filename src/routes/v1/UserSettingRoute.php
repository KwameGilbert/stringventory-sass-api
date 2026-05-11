<?php

declare(strict_types=1);

use App\Controllers\UserSettingController;
use Slim\App;

return function (App $app): void {
    $userSettingController = $app->getContainer()->get(UserSettingController::class);
    
    $app->group('/v1/user-settings', function ($group) use ($userSettingController) {
        $group->get('', [$userSettingController, 'index']);
        $group->get('/{id}', [$userSettingController, 'show']);
        $group->post('', [$userSettingController, 'create']);
        $group->put('/{id}', [$userSettingController, 'update']);
        $group->delete('/{id}', [$userSettingController, 'delete']);
    });
};
