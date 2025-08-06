<?php

namespace App\Message\Command\App\Notification;

use App\Entity\User;
use App\Model\PushNotification\Notification;

class SendPushNotification
{

    public function __construct(
        public User $user,
        public Notification $notification,
    ) {
    }

}
