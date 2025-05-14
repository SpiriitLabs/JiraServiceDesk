<?php

namespace App\Message\Query\App\Project;

class GetProjectRolesByJiraKey
{
    public function __construct(
        public string $jiraKey,
    ) {
    }
}
