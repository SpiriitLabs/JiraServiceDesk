<?php

namespace App\Model;

class SearchIssuesResult
{
    public function __construct(
        public ?int $total = null,
        public array $issues = [],
        public ?string $nextPageToken = null,
    ) {
    }
}
