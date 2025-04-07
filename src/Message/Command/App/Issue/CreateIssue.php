<?php

namespace App\Message\Command\App\Issue;

use App\Entity\Project;
use App\Entity\User;

class CreateIssue extends AbstractIssueDTO
{
    public function __construct(
        public Project $project,
        public User $creator,
        public array $attachments = [],
        public ?string $description = null,
    ) {
        parent::__construct(
            summary: '',
        );
    }
}
