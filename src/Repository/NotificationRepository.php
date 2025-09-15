<?php

namespace App\Repository;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 */
class NotificationRepository extends AbstractEntityRepository
{
    public const int LIMIT_USER_NOTIFICATIONS = 10;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    public function getLastsByUser(User $user): array
    {
        return $this->findBy(
            [
                'user' => $user,
            ],
            [
                'sendAt' => 'DESC',
            ],
            self::LIMIT_USER_NOTIFICATIONS
        );
    }
}
