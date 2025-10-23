<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use App\Enum\User\Role;
use App\Enum\User\Theme;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public const string SUPER_ADMIN_USER_REFERENCE = 'super_admin_user';

    public const string USER_REFERENCE = 'user';

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadUserInReference(
            email: 'superadmin@email.tld',
            firstName: 'Super',
            lastName: 'Admin',
            roles: [Role::ROLE_USER, Role::ROLE_ADMIN, Role::ROLE_APP_CAN_ASSIGNEE, Role::ROLE_APP_CAN_VIEW_BACKLOG],
            reference: self::SUPER_ADMIN_USER_REFERENCE,
            manager: $manager
        );

        $this->loadUserInReference(
            email: 'user@email.tld',
            firstName: 'Uti',
            lastName: 'Lisateur',
            roles: [Role::ROLE_USER, Role::ROLE_APP_CAN_ASSIGNEE, Role::ROLE_APP_CAN_VIEW_BACKLOG],
            reference: self::USER_REFERENCE,
            manager: $manager
        );
    }

    private function loadUserInReference(
        string $email,
        string $firstName,
        string $lastName,
        array $roles,
        string $reference,
        ObjectManager $manager,
    ): void {
        $user = new User(
            email: $email,
            firstName: $firstName,
            lastName: $lastName,
        );
        $user->setRoles($roles);
        $user->preferredTheme = Theme::DARK;
        $user->preferenceNotification = true;
        $user->preferenceNotificationIssueCreated = true;
        $user->preferenceNotificationIssueUpdated = true;
        $user->preferenceNotificationCommentCreated = true;
        $user->preferenceNotificationCommentUpdated = true;

        $password = $this->passwordHasher->hashPassword($user, 'password');
        $user->setPassword($password);

        $manager->persist($user);
        $manager->flush();

        $this->addReference($reference, $user);
    }
}
