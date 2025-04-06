<?php

namespace App\Message\Query\App\Issue;

use App\Entity\Project;
use JiraCloud\Issue\Issue;

class GetIssueAssignableUsers
{
    public function __construct(
        public ?Issue $issue = null,
        public ?Project $project = null,
    ) {
    }
}
