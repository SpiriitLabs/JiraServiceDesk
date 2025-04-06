<?php

namespace App\Message\Query\App\Issue\Handler;

use App\Message\Query\App\Issue\GetIssueAssignableUsers;
use App\Repository\Jira\UserRepository;
use JiraCloud\User\User;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class GetIssueAssignableUsersHandler
{
    public function __construct(
        private UserRepository $userRepository,
        #[Autowire(env: 'JIRA_ACCOUNT_ID')]
        private string $jiraAPIAccountId,
    ) {
    }

    public function __invoke(GetIssueAssignableUsers $query): array
    {
        $jiraCanAssignable = $this->userRepository->getAssignableUser(
            $query->issue->key,
            $query->project?->jiraKey ?? null
        );
        $result = [];

        foreach ($jiraCanAssignable as $user) {
            /** @var User $user */
            $result[$user->accountId] = $user->displayName;
        }
        $result[$this->jiraAPIAccountId] = 'Vous';
        $result['null'] = 'Non Assignée';

        return array_reverse(array_flip($result));
    }
}
