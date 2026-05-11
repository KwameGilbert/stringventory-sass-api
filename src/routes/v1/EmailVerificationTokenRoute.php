<?php

declare(strict_types=1);

use App\Controllers\EmailVerificationTokenController;
use Slim\App;

return function (App $app): void {
    $emailVerificationTokenController = $app->getContainer()->get(EmailVerificationTokenController::class);
    
    $app->group('/v1/email-verification-tokens', function ($group) use ($emailVerificationTokenController) {
        $group->get('', [$emailVerificationTokenController, 'index']);
        $group->get('/{id}', [$emailVerificationTokenController, 'show']);
        $group->post('', [$emailVerificationTokenController, 'create']);
        $group->put('/{id}', [$emailVerificationTokenController, 'update']);
        $group->delete('/{id}', [$emailVerificationTokenController, 'delete']);
    });
};
