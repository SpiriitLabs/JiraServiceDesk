<?php

declare(strict_types=1);

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
