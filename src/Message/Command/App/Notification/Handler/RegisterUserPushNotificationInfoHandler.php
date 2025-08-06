<?php

namespace App\Message\Command\App\Notification\Handler;

use App\Message\Command\App\Notification\RegisterUserPushNotificationInfo;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class RegisterUserPushNotificationInfoHandler
{

    public function __invoke(RegisterUserPushNotificationInfo $command): void
    {
        $command->user->pushNotificationInfo->endpoint = $command->endpoint;
        $command->user->pushNotificationInfo->p256dh = $command->p256dh;
        $command->user->pushNotificationInfo->auth = $command->auth;
    }

}
