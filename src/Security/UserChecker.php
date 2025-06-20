<?php

namespace App\Security;

use App\Entity\User;
use App\Enum\User\Locale;
use App\Enum\User\Role;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserChecker implements UserCheckerInterface
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly Security $security,
    ) {
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }

    public function checkPreAuth(UserInterface $user): void
    {
        if (! $user instanceof User) {
            return;
        }

        if ($user->enabled == false) {
            throw new CustomUserMessageAccountStatusException(message: $this->translator->trans(
                id: 'security.login.account_disable',
                domain: 'app',
                locale: $user->preferredLocale?->value ?? Locale::FR->value,
            ), );
        }

        if (($this->security->isGrantedForUser(
            $user,
            Role::ROLE_ADMIN
        ) == false) && $user->getProjects()
            ->count() == 0) {
            throw new CustomUserMessageAccountStatusException(message: $this->translator->trans(
                id: 'security.login.user_account_no_projects',
                domain: 'app',
                locale: $user->preferredLocale?->value ?? Locale::FR->value,
            ), );
        }
    }
}
