<?php

namespace App\Message\Command\App\Notification;

class NotificationViewed
{
    public function __construct(
        public string $notificationId,
    ) {
    }
}
