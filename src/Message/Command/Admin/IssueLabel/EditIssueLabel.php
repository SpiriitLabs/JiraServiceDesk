<?php

declare(strict_types=1);

namespace App\Message\Command\Admin\IssueLabel;

use App\Entity\IssueLabel;

class EditIssueLabel extends AbstractIssueLabelDTO
{
    public function __construct(
        public IssueLabel $issueLabel,
        public string $jiraLabel = '',
        public string $name = '',
        public array $users = [],
    ) {
        parent::__construct(
            jiraLabel: $this->issueLabel->jiraLabel,
            name: $this->issueLabel->name,
            users: $this->issueLabel->getUsers()
                ->toArray(),
        );
    }
}
