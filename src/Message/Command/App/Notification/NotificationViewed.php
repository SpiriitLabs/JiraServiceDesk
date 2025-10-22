<?php

declare(strict_types=1);

namespace App\Message\Command\App\Notification;

class NotificationViewed
{
    public function __construct(
        public string $notificationId,
    ) {
    }
}
