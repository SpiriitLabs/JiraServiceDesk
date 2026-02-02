<?php

declare(strict_types=1);

namespace App\Message\Trait;

use App\Entity\User;
use App\Message\Command\User\AbstractUserDTO;

trait UserHandlerTrait
{
    protected function updateUserFields(User $user, AbstractUserDTO $command): void
    {
        $user->setRoles($command->roles);
        $user->preferredLocale = $command->preferredLocale;
        $user->preferredTheme = $command->preferredTheme;
        $user->preferenceNotificationIssueCreated = $command->preferenceNotificationIssueCreated;
        $user->preferenceNotificationIssueUpdated = $command->preferenceNotificationIssueUpdated;
        $user->preferenceNotificationCommentCreated = $command->preferenceNotificationCommentCreated;
        $user->preferenceNotificationCommentUpdated = $command->preferenceNotificationCommentUpdated;
        $user->preferenceNotificationCommentOnlyOnTag = $command->preferenceNotificationCommentOnlyOnTag;
        $user->slackBotToken = $command->slackBotToken;
        $user->slackMemberId = $command->slackMemberId;

        $user->clearIssueLabels();
        foreach ($command->issueLabels as $issueLabel) {
            $user->addIssueLabel($issueLabel);
        }
    }
}
