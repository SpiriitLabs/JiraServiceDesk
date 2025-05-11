<?php

namespace App\Model\Filter;

use App\Model\Filter\Trait\FilterQueryTrait;

class IssueFilter
{
    use FilterQueryTrait;

    public function __construct(
        public array $projects = [],
    ) {
    }
}
