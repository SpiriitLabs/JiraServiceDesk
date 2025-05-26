<?php

namespace App\Message\Command\App\Issue;

use App\Entity\IssueType;
use App\Entity\Priority;
use App\Entity\Project;

class AbstractIssueDTO
{
    public function __construct(
        public string $summary,
        public Project $project,
        public ?Priority $priority = null,
        public ?IssueType $type = null,
        public ?string $assignee = null,
    ) {
    }
}
