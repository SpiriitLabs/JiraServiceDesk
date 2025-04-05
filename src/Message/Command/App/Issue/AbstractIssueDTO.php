<?php

namespace App\Message\Command\App\Issue;

use App\Entity\IssueType;
use App\Enum\Issue\Priority;

class AbstractIssueDTO
{
    public function __construct(
        public string $summary,
        public Priority $priority = Priority::NORMAL,
        public ?IssueType $type = null,
    ) {
    }
}
