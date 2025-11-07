<?php

declare(strict_types=1);

namespace App\Cli;

use App\Entity\IssueLabel;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validation;

#[AsCommand(name: 'app:create-issue-label:default')]
class CreateDefaultIssueLabelCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $name = $io->ask('Nom du label ?', null, Validation::createCallable(new NotBlank()));
        $jiraLabel = $io->ask('Nom machine dans Jira ?', null, Validation::createCallable(new NotBlank()));

        $issueLabel = new IssueLabel(
            jiraLabel: $jiraLabel,
            name: $name,
        );

        $users = $this->userRepository->findAll();
        foreach ($users as $user) {
            $issueLabel->addUser($user);
        }

        $this->entityManager->persist($issueLabel);
        $this->entityManager->flush();

        $io->success(sprintf('Created issue label "%s"', $jiraLabel));

        return self::SUCCESS;
    }
}
