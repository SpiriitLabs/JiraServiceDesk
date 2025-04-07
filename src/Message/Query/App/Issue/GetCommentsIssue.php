<?php

namespace App\Message\Query\App\Issue;

class GetCommentsIssue
{
    public function __construct(
        public string $issueId,
    ) {
    }
}
