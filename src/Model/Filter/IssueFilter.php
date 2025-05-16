<?php

namespace App\Model\Filter;

use App\Model\Filter\Trait\FilterQueryTrait;

class IssueFilter
{
    use FilterQueryTrait;

    /**
     * @param array<int,\App\Entity\Project> $projects
     * @param array<int,mixed>               $statusesIds
     */
    public function __construct(
        public array $projects = [],
        public array $statusesIds = [],
    ) {
    }
}
