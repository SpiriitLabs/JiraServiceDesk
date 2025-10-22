<?php

declare(strict_types=1);

namespace App\Message\Query\App\Project;

class GetProjectStatusesByJiraKey
{
    public function __construct(
        public string $jiraKey,
    ) {
    }
}
