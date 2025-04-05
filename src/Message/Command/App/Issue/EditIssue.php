<?php

namespace App\Message\Command\App\Issue;

use App\Entity\IssueType;
use App\Entity\Project;
use App\Enum\Issue\Priority;
use JiraCloud\Issue\Issue;

class EditIssue extends AbstractIssueDTO
{
    public function __construct(
        public Project $project,
        public Issue $issue,
        IssueType $issueType,
        public string $transition,
    ) {
        parent::__construct(
            summary: $this->issue->fields->summary,
            priority: Priority::from($this->issue->fields->priority->name),
            type: $issueType,
        );
    }
}
