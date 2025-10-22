<?php

declare(strict_types=1);

namespace App\Message\Command\User\Handler;

use App\Entity\User;
use App\Message\Command\User\EditUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsMessageHandler]
readonly class EditUserHandler
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(EditUser $command): User
    {
        $user = $command->user;

        $user->setFirstName($command->firstName);
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
        $user->preferenceNotificationCommentOnlyOnTag = $command->preferenceNotificationCommentOnlyOnTag;
        $user->enabled = $command->enabled;
        $user->defaultProject = $command->defaultProject;

        $deletedProjects = array_diff($user->getProjects()->toArray(), $command->projects);
        if (
            count($deletedProjects) > 0
            || count(array_diff($command->projects, $user->getProjects()->toArray())) > 0
        ) {
            $user->clearProjects();

            foreach ($command->projects as $project) {
                $user->addProject($project);
            }

            foreach ($deletedProjects as $deletedProject) {
                if ($user->defaultProject !== null && $user->defaultProject->getId() === $deletedProject->getId()) {
                    $user->defaultProject = null;
                }

                foreach ($user->getFavorites() as $userFavorite) {
                    if ($userFavorite->getProject()->getId() === $deletedProject->getId()) {
                        $user->removeFavorite($userFavorite);
                        $this->entityManager->remove($userFavorite);
                        $this->entityManager->flush();
                    }
                }
            }
        }

        if ($command->plainPassword !== null) {
            $password = $this->passwordHasher->hashPassword($user, $command->plainPassword);
            $user->setPassword($password);
        }

        return $user;
    }
}
