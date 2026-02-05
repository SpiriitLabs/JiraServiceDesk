<?php

declare(strict_types=1);

namespace App\Message\Command\User;

use App\Entity\Project;
use App\Enum\Notification\NotificationChannel;
use App\Enum\User\Locale;
use App\Enum\User\Theme;

class AbstractUserDTO
{
    /**
     * @param array<int,mixed>      $roles
     * @param array<int,mixed>      $projects
     * @param NotificationChannel[] $preferenceNotificationIssueCreated
     * @param NotificationChannel[] $preferenceNotificationIssueUpdated
     * @param NotificationChannel[] $preferenceNotificationCommentCreated
     * @param NotificationChannel[] $preferenceNotificationCommentUpdated
     * @param NotificationChannel[] $preferenceNotificationCommentOnlyOnTag
     */
    public function __construct(
        public string $email,
        public string $lastName,
        public string $firstName,
        public array $roles = [],
        public array $projects = [],
        public ?string $plainPassword = null,
        public ?string $company = null,
        public Locale $preferredLocale = Locale::FR,
        public Theme $preferredTheme = Theme::AUTO,
        public array $preferenceNotificationIssueCreated = [NotificationChannel::IN_APP, NotificationChannel::EMAIL],
        public array $preferenceNotificationIssueUpdated = [NotificationChannel::IN_APP, NotificationChannel::EMAIL],
        public array $preferenceNotificationCommentCreated = [NotificationChannel::IN_APP, NotificationChannel::EMAIL],
        public array $preferenceNotificationCommentUpdated = [NotificationChannel::IN_APP, NotificationChannel::EMAIL],
        public array $preferenceNotificationCommentOnlyOnTag = [],
        public bool $enabled = true,
        public ?Project $defaultProject = null,
        public array $issueLabels = [],
        public ?string $slackBotToken = null,
        public ?string $slackMemberId = null,
    ) {
    }
}
