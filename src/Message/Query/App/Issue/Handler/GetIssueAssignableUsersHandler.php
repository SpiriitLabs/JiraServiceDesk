<?php

declare(strict_types=1);

namespace App\Message\Query\App\Issue\Handler;

use App\Message\Query\App\Issue\GetIssueAssignableUsers;
use App\Repository\Jira\UserRepository;
use JiraCloud\User\User;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class GetIssueAssignableUsersHandler
{
    protected const int CACHE_DURATION = 7200;

    public function __construct(
        private UserRepository $userRepository,
        #[Autowire(env: 'JIRA_ACCOUNT_ID')]
        private string $jiraAPIAccountId,
    ) {
    }

    public function __invoke(
        GetIssueAssignableUsers $query,
    ): array {
        $cache = new FilesystemAdapter();
        $cacheAssignableUsers = $cache->getItem(sprintf('jira.assignable_users_%s', $query->project->jiraKey));

        if ($cacheAssignableUsers->isHit()) {
            return $cacheAssignableUsers->get();
        }

        $jiraCanAssignable = $this->userRepository->getAssignableUser(
            $query->project
        );
        $result = [];

        foreach ($jiraCanAssignable as $user) {
            /** @var User $user */
            $result[$user->accountId] = $user->displayName;
        }
        $result[$this->jiraAPIAccountId] = sprintf(
            '%s (Support)',
            $query->user->getFullName(),
        );
        $result['null'] = 'Non Assignée';
        $result = array_reverse(array_flip($result));

        $cacheAssignableUsers->set($result);
        $cacheAssignableUsers->expiresAfter(self::CACHE_DURATION);
        $cache->save($cacheAssignableUsers);

        return $result;
    }
}
