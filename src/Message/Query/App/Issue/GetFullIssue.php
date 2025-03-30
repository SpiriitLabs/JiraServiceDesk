<?php

namespace App\Message\Query\App\Issue;

class GetFullIssue
{
    public function __construct(
        public string $issueId,
    ) {
    }
}
