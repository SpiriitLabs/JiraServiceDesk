<?php

namespace App\Message\Command\App\Issue;

use App\Entity\IssueType;
use App\Entity\Priority;

class AbstractIssueDTO
{
    public function __construct(
        public string $summary,
        public ?Priority $priority = null,
        public ?IssueType $type = null,
    ) {
    }
}
