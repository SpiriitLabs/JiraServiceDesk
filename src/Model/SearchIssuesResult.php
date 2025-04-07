<?php

namespace App\Model;

class SearchIssuesResult
{
    public function __construct(
        public int $page = 1,
        public int $total = -1,
        public array $issues = [],
    ) {
    }
}
