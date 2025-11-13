<?php

declare(strict_types=1);

namespace App\Message\Command\User\Handler;

use App\Entity\User;
use App\Exception\User\CurrentPasswordWrongException;
use App\Exception\User\PasswordAlreadyUseException;
use App\Message\Command\User\ChangePasswordUser;
use App\Message\Trait\UserHandlerTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsMessageHandler]
readonly class ChangePasswordUserHandler
{
    use UserHandlerTrait;

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(ChangePasswordUser $command): User
    {
        $user = $command->user;

        if (! $this->passwordHasher->isPasswordValid($user, $command->currentPlainPassword)) {
            throw new CurrentPasswordWrongException();
        }

        if ($command->currentPlainPassword === $command->newPlainPassword) {
            throw new PasswordAlreadyUseException();
        }

        $password = $this->passwordHasher->hashPassword($user, $command->newPlainPassword);
        $user->setPassword($password);

        return $user;
    }
}
