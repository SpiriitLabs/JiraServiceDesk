<?php

namespace App\Message\Command\User;

use App\Entity\User;

class EditUser extends AbstractUserDTO
{
    public function __construct(
        public readonly User $user,
    ) {
        parent::__construct(
            email: $this->user->email,
            lastName: $this->user->getLastName(),
            firstName: $this->user->firstName,
            roles: $this->user->getRoles(),
            projects: $this->user->getProjects()
                ->toArray(),
            company: $this->user->company,
            preferedLocale: $this->user->preferredLocale,
            preferedTheme: $this->user->preferredTheme,
            preferenceNotification: $this->user->preferenceNotification,
            preferenceNotificationIssueCreated: $this->user->preferenceNotificationIssueCreated,
            preferenceNotificationIssueUpdated: $this->user->preferenceNotificationIssueUpdated,
            preferenceNotificationCommentCreated: $this->user->preferenceNotificationCommentCreated,
            preferenceNotificationCommentUpdated: $this->user->preferenceNotificationCommentUpdated,
            enabled: $this->user->enabled,
            defaultProject: $this->user->defaultProject,
        );
    }
}
