<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Notification;
use App\Enum\Notification\NotificationType;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Notification>
 */
final class NotificationFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return Notification::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        return [
            'notificationType' => self::faker()->randomElement(NotificationType::cases()),
            'subject' => self::faker()->text(),
            'body' => self::faker()->text(255),
            'link' => self::faker()->text(),
            'user' => UserFactory::new(),
            'isViewed' => self::faker()->boolean(),
        ];
    }

    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function (Notification $notification): void {
                $reflection = new \ReflectionClass($notification);
                $property = $reflection->getProperty('sendAt');
                $property->setAccessible(true);
                $property->setValue($notification, \DateTimeImmutable::createFromMutable(self::faker()->dateTime()));

                $notification->user->addNotification($notification);
            })
        ;
    }
}
