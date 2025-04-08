<?php

namespace App\Model\Filter;

use App\Entity\Project;
use App\Model\Filter\Trait\FilterQueryTrait;

class IssueFilter
{
    use FilterQueryTrait;

    public function __construct(
        public ?Project $project = null,
    ) {
    }
}
