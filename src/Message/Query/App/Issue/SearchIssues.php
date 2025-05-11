<?php

namespace App\Message\Query\App\Issue;

use App\Entity\User;
use App\Model\Filter\IssueFilter;
use App\Model\SortParams;

class SearchIssues
{
    public const int MAX_ISSUES_RESULTS = 50;

    public SortParams $sort;

    public function __construct(
        string $sort,
        public ?User $user = null,
        public bool $onlyUserAssigned = false,
        public ?IssueFilter $filter = null,
        public int $maxIssuesResults = self::MAX_ISSUES_RESULTS,
        public ?string $pageToken = null,
    ) {
        $this->sort = SortParams::createSort($sort);

        if ($this->filter === null && $this->user !== null) {
            $this->filter = new IssueFilter(projects: $this->user->getProjects()->toArray());
        }
    }
}
