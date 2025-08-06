<?php

namespace App\Message\Command\App\Notification;

use App\Entity\User;

class RegisterUserPushNotificationInfo
{

    public function __construct(
        public User $user,
        public string $endpoint,
        public string $p256dh,
        public string $auth,
    ) {
    }

}
