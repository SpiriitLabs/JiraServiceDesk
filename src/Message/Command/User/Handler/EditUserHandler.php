<?php

namespace App\Message\Command\User\Handler;

use App\Entity\User;
use App\Message\Command\User\EditUser;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsMessageHandler]
readonly class EditUserHandler
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function __invoke(EditUser $command): User
    {
        $user = $command->user;

        $user->firstName = $command->firstName;
        $user->setLastName($command->lastName);
        $user->company = $command->company;
        $user->setRoles($command->roles);
        $user->preferredLocale = $command->preferedLocale;
        $user->preferredTheme = $command->preferedTheme;
        $user->preferenceNotification = $command->preferenceNotification;
        $user->preferenceNotificationIssueCreated = $command->preferenceNotificationIssueCreated;
        $user->preferenceNotificationIssueUpdated = $command->preferenceNotificationIssueUpdated;
        $user->preferenceNotificationCommentCreated = $command->preferenceNotificationCommentCreated;
        $user->preferenceNotificationCommentUpdated = $command->preferenceNotificationCommentUpdated;

        if (
            count(array_diff($user->getProjects()->toArray(), $command->projects)) > 0
            || count(array_diff($command->projects, $user->getProjects()->toArray())) > 0
        ) {
            $user->clearProjects();


            foreach ($command->projects as $project) {
                $user->addProject($project);
            }
        }

        if ($command->plainPassword !== null) {
            $password = $this->passwordHasher->hashPassword($user, $command->plainPassword);
            $user->setPassword($password);
        }

        return $user;
    }
}
