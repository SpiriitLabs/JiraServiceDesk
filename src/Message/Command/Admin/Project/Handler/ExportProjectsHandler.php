<?php

namespace App\Message\Command\Admin\Project\Handler;

use App\Message\Command\Admin\Project\ExportProjects;
use App\Repository\ProjectRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Serializer\Encoder\EncoderInterface;

#[AsMessageHandler]
readonly class ExportProjectsHandler
{
    public function __construct(
        private EncoderInterface $csvEncoder,
        private ProjectRepository $projectRepository,
    ) {
    }

    public function __invoke(ExportProjects $command): string
    {
        $projects = $this->projectRepository->findAll();

        $content = [];

        foreach ($projects as $project) {
            $content[] = [
                'nom' => $project->name,
                'description' => $project->description,
                'jira id' => $project->jiraId,
                'jira key' => $project->jiraKey,
                'Nombre utilisateurs' => count($project->getUsers()),
            ];
        }

        return $this->csvEncoder->encode($content, 'csv');
    }
}
