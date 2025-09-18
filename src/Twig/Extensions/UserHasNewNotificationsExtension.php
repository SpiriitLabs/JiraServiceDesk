<?php

namespace App\Twig\Extensions;

use App\Entity\User;
use App\Repository\NotificationRepository;
use Twig\Attribute\AsTwigFunction;

class UserHasNewNotificationsExtension
{
    public function __construct(
        private readonly NotificationRepository $notificationRepository,
    ) {
    }

    #[AsTwigFunction(
        name: 'user_has_new_notifications',
    )]
    public function userHasNewNotifications(User $user): bool
    {
        $notifications = $this->notificationRepository->getLastsByUser($user);

        return ! empty($notifications);
    }
}
