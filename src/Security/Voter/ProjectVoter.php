<?php

declare(strict_types=1);

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class ProjectVoter extends Voter
{
    public const PROJECT_ACCESS = 'PROJECT_ACCESS';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::PROJECT_ACCESS])
            && $subject instanceof \App\Entity\Project;
    }

    /**
     * @param \App\Entity\Project $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var \App\Entity\User $user */
        $user = $token->getUser();

        if (! $user instanceof UserInterface) {
            return false;
        }

        return $user->getProjects()
            ->contains($subject)
        ;
    }
}
