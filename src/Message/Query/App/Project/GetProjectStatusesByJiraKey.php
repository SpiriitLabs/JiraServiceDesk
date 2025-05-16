<?php

namespace App\Message\Query\App\Project;

class GetProjectStatusesByJiraKey
{
    public function __construct(
        public string $jiraKey,
    ) {
    }
}
