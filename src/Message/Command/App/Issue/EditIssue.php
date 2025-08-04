<?php

namespace App\Message\Command\App\Issue;

use App\Entity\IssueType;
use App\Entity\Priority;
use App\Entity\Project;
use JiraCloud\Issue\Issue;

class EditIssue extends AbstractIssueDTO
{
    public function __construct(
        Project $project,
        public Issue $issue,
        IssueType $issueType,
        Priority $priority,
        public string $transition,
        public ?string $assignee = 'null',
    ) {
        parent::__construct(
            summary: $this->issue->fields->summary,
            project: $project,
            priority: $priority,
            type: $issueType,
        );
    }
}
