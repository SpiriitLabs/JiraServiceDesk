<?php

namespace App\Twig\Extensions;

use App\Entity\Notification;
use App\Entity\User;
use App\Repository\NotificationRepository;
use Twig\Attribute\AsTwigFunction;

class UserHasNewNotificationsExtension
{
    public function __construct(
        private NotificationRepository $notificationRepository,
    ) {
    }

    #[AsTwigFunction(
        name: 'user_has_new_notifications',
    )]
    public function userHasNewNotifications(User $user): bool
    {
        $notifications = $this->notificationRepository->getLastsByUser($user);
        $notifications = array_filter($notifications, function (Notification $notification) {
            return $notification->isViewed === false;
        });

        return ! empty($notifications);
    }
}
