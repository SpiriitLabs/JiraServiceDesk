<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\User;
use App\Enum\Notification\NotificationChannel;
use App\Enum\User\Locale;
use App\Enum\User\Theme;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<User>
 */
final class UserFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return User::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            'createdAt' => self::faker()->dateTime(),
            'email' => self::faker()->text(180),
            'enabled' => self::faker()->boolean(),
            'firstName' => self::faker()->text(255),
            'lastName' => self::faker()->text(255),
            'password' => self::faker()->text(),
            'preferenceNotificationIssueCreated' => [NotificationChannel::IN_APP, NotificationChannel::EMAIL],
            'preferenceNotificationIssueUpdated' => [NotificationChannel::IN_APP, NotificationChannel::EMAIL],
            'preferenceNotificationIssueDeleted' => [NotificationChannel::IN_APP, NotificationChannel::EMAIL],
            'preferenceNotificationCommentCreated' => [NotificationChannel::IN_APP, NotificationChannel::EMAIL],
            'preferenceNotificationCommentUpdated' => [NotificationChannel::IN_APP, NotificationChannel::EMAIL],
            'preferenceNotificationCommentOnlyOnTag' => [],
            'preferredLocale' => self::faker()->randomElement(Locale::cases()),
            'preferredTheme' => self::faker()->randomElement(Theme::cases()),
            'roles' => [],
            'updatedAt' => self::faker()->dateTime(),
        ];
    }

    #[\Override]
    protected function initialize(): static
    {
        return $this;
    }
}
