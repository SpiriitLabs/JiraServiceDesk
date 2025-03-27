<?php

namespace App\Message\Command\App\Issue;

use JiraCloud\Issue\Issue;

class CreateComment
{
    public function __construct(
        public Issue $issue,
        public string $comment,
        public array $attachments,
    ) {
    }
}
