<?php

declare(strict_types=1);

namespace App\Model\Filter;

use App\Model\Filter\Trait\FilterQueryTrait;

class IssueFilter
{
    use FilterQueryTrait;

    public function __construct(
        public array $projects = [],
        public ?array $statusesIds = null,
        public ?array $assigneeIds = null,
        public bool $hasResolvedMasked = false,
    ) {
    }
}
