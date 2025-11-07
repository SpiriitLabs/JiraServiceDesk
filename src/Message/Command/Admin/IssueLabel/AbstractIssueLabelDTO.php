<?php

declare(strict_types=1);

namespace App\Message\Command\Admin\IssueLabel;

class AbstractIssueLabelDTO
{
    public function __construct(
        public string $jiraLabel = '',
        public string $name = '',
        public array $users = [],
    ) {
    }
}
