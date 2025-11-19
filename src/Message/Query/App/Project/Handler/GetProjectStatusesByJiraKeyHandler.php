<?php

declare(strict_types=1);

namespace App\Message\Query\App\Project\Handler;

use App\Message\Query\App\Project\GetProjectStatusesByJiraKey;
use App\Repository\Jira\ProjectRepository;
use JiraCloud\Issue\IssueStatus;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class GetProjectStatusesByJiraKeyHandler
{
    protected const int CACHE_DURATION = 7200;

    public function __construct(
        private ProjectRepository $projectRepository,
    ) {
    }

    public function __invoke(GetProjectStatusesByJiraKey $query): array
    {
        $cache = new FilesystemAdapter();
        $cacheJiraStatuses = $cache->getItem(sprintf('jira.project_status_%s', $query->jiraKey));

        if ($cacheJiraStatuses->isHit()) {
            return $cacheJiraStatuses->get();
        }

        $jiraTypes = $this->projectRepository->getStatuses($query->jiraKey);
        $jiraStatuses = [];

        foreach ($jiraTypes as $jiraType) {
            /** @var IssueStatus $jiraStatus */
            foreach ($jiraType->statuses as $jiraStatus) {
                $jiraStatuses[$jiraStatus->name] = $jiraStatus->id;
            }
        }

        $cacheJiraStatuses->set($jiraStatuses);
        $cacheJiraStatuses->expiresAfter(self::CACHE_DURATION);
        $cache->save($cacheJiraStatuses);

        return $jiraStatuses;
    }
}
