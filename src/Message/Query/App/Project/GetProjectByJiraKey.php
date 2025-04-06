<?php

namespace App\Message\Query\App\Project;

class GetProjectByJiraKey
{
    public function __construct(
        public string $jiraKey,
    ) {
    }
}
