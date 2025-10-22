<?php

declare(strict_types=1);

namespace App\Message\Command\Admin\Project;

use App\Entity\Project;

class GenerateProjectIssueTypes
{
    public function __construct(
        public Project $project,
        public ?array $jiraIssueTypes = null,
    ) {
    }
}
