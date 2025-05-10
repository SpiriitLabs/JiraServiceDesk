<?php

declare(strict_types=1);

namespace App\Cli;

use App\Enum\User\Role;
use App\Message\Command\User\CreateUser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validation;

#[AsCommand(name: 'app:super-user:new')]
class CreateSuperUserCommand extends Command
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $io->ask('Username / email ?', null, Validation::createCallable(new NotBlank(), new Email()));

        $password = $io->ask('Password ?', null, Validation::createCallable(new NotBlank()));

        $lastName = $io->ask('Nom de famille ?', null, Validation::createCallable(new NotBlank()));
        $firstName = $io->ask('Prénom ?', null, Validation::createCallable(new NotBlank()));

        $this->commandBus->dispatch(
            new CreateUser(
                email: $email,
                lastName: $lastName,
                firstName: $firstName,
                roles: [Role::ROLE_USER, Role::ROLE_ADMIN],
                plainPassword: $password,
            )
        );

        $io->success(sprintf('Created super admin user "%s"', $email));

        return self::SUCCESS;
    }
}
