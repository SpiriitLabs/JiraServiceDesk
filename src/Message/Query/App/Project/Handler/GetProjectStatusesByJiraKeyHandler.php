<?php

namespace App\Message\Query\App\Project\Handler;

use App\Message\Query\App\Project\GetProjectStatusesByJiraKey;
use App\Repository\Jira\ProjectRepository;
use JiraCloud\Issue\IssueStatus;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GetProjectStatusesByJiraKeyHandler
{
    public function __construct(
        private ProjectRepository $projectRepository,
    ) {
    }

    public function __invoke(GetProjectStatusesByJiraKey $query): array
    {
        $jiraTypes = $this->projectRepository->getStatuses($query->jiraKey);
        $jiraStatuses = [];

        foreach ($jiraTypes as $jiraType) {
            /** @var IssueStatus $jiraStatus */
            foreach ($jiraType->statuses as $jiraStatus) {
                $jiraStatuses[$jiraStatus->name] = $jiraStatus->id;
            }
        }

        return $jiraStatuses;
    }
}
