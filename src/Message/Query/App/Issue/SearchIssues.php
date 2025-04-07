<?php

namespace App\Message\Query\App\Issue;

use App\Entity\User;
use App\Model\SortParams;

class SearchIssues
{
    public const int MAX_ISSUES_RESULTS = 50;

    public SortParams $sort;

    public function __construct(
        string $sort,
        public int $page = 1,
        public ?User $user = null,
        public bool $onlyUserAssigned = false,
    ) {
        $this->sort = SortParams::createSort($sort);
    }
}
