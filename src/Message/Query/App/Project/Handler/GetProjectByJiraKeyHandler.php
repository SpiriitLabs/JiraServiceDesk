<?php

declare(strict_types=1);

namespace App\Message\Query\App\Project\Handler;

use App\Entity\Project;
use App\Message\Query\App\Project\GetProjectByJiraKey;
use App\Repository\ProjectRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class GetProjectByJiraKeyHandler
{
    public function __construct(
        private ProjectRepository $projectRepository,
    ) {
    }

    public function __invoke(GetProjectByJiraKey $query): ?Project
    {
        return $this->projectRepository->findOneBy([
            'jiraKey' => $query->jiraKey,
        ]);
    }
}
