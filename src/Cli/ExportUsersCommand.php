<?php

declare(strict_types=1);

namespace App\Cli;

use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

#[AsCommand(name: 'app:users:export')]
class ExportUsersCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $progressBar = $io->createProgressBar();

        $users = $this->userRepository->findAll();

        $progressBar->setMaxSteps(\count($users));
        $progressBar->start();

        $filesystem = new Filesystem();
        $csvExport = Path::makeRelative('var/users.csv', '/');
        $filesystem->remove($csvExport);
        $filesystem->touch($csvExport);

        $csvWriter = fopen($csvExport, 'w');

        if ($csvWriter === false) {
            return self::FAILURE;
        }

        fputcsv($csvWriter, [
            'email',
            'nom',
            'prénom',
            'société',
            'projets',
        ]);
        foreach ($users as $user) {
            $progressBar->advance();

            $projects = array_map(function ($project) {
                return $project->jiraKey;
            }, $user->getProjects()
                ->toArray());

            fputcsv($csvWriter, [
                $user->email,
                $user->getLastName(),
                $user->firstName,
                $user->company,
                implode(', ', $projects),
            ]);
        }

        $progressBar->finish();

        $io->success(sprintf('Export finished : %s users exported"', count($users)));

        return self::SUCCESS;
    }
}
