<?php

namespace App\Message\Query\App\Issue;

use App\Entity\Project;

class GetIssueAssignableUsers
{
    public function __construct(
        public ?Project $project = null,
    ) {
    }
}
