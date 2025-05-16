<?php

namespace App\Model;

class SearchIssuesResult
{
    /**
     * @param array<mixed> $issues
     */
    public function __construct(
        public ?int $total = null,
        public array $issues = [],
        public ?string $nextPageToken = null,
    ) {
    }
}
