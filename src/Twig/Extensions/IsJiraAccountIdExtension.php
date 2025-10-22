<?php

namespace App\Twig\Extensions;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Attribute\AsTwigFunction;

readonly class IsJiraAccountIdExtension
{
    public function __construct(
        #[Autowire(env: 'JIRA_ACCOUNT_ID')]
        private string $jiraAPIAccountId,
    ) {
    }

    #[AsTwigFunction(
        name: 'is_jira_account_id'
    )]
    public function isJiraAccountId(string|int $currentAccountId): bool
    {
        return $currentAccountId == $this->jiraAPIAccountId;
    }
}
