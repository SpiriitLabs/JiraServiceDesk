<?php

namespace App\Message\Command\Admin\User\Handler;

use App\Message\Command\Admin\User\ExportUsers;
use App\Repository\UserRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Serializer\Encoder\CsvEncoder;

#[AsMessageHandler]
class ExportUsersHandler
{
    public function __construct(
        #[Autowire(service: 'serializer.encoder.csv')]
        private readonly CsvEncoder $csvEncoder,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function __invoke(ExportUsers $command): string
    {
        $users = $this->userRepository->findAll();

        $content = [];

        foreach ($users as $user) {
            $projects = array_map(function ($project) {
                return $project->jiraKey;
            }, $user->getProjects()
                ->toArray());

            $content[] = [
                'email' => $user->email,
                'nom' => $user->getLastName(),
                'prénom' => $user->firstName,
                'société' => $user->company,
                'projets' => implode(', ', $projects),
            ];
        }

        return $this->csvEncoder->encode($content, 'csv');
    }
}
