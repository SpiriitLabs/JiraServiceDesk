<?php

declare(strict_types=1);

namespace App\Message\Query\App\Project\Handler;

use App\Message\Query\App\Project\GetProjectRolesByJiraKey;
use App\Repository\Jira\ProjectRepository;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class GetProjectRolesByJiraKeyHandler
{
    protected const int CACHE_DURATION = 7200;

    public function __construct(
        private ProjectRepository $projectRepository,
    ) {
    }

    public function __invoke(GetProjectRolesByJiraKey $query): array
    {
        $cache = new FilesystemAdapter();
        $cacheJiraRoles = $cache->getItem(sprintf('jira.project_roles_%s', $query->jiraKey));

        if ($cacheJiraRoles->isHit()) {
            return $cacheJiraRoles->get();
        }

        $jiraRoles = $this->projectRepository->getRoles($query->jiraKey);

        foreach ($jiraRoles as $jiraRoleName => $jiraRoleSelf) {
            $jiraRoleSelfArray = explode('/', rtrim($jiraRoleSelf, '/'));
            $jiraRoles[$jiraRoleName] = end($jiraRoleSelfArray);
        }

        $cacheJiraRoles->set($jiraRoles);
        $cacheJiraRoles->expiresAfter(self::CACHE_DURATION);
        $cache->save($cacheJiraRoles);

        return $jiraRoles;
    }
}
