<?php

declare(strict_types=1);

namespace App\Message\Command\App\Issue;

use App\Entity\User;
use JiraCloud\Issue\Issue;

class CreateComment
{
    public function __construct(
        public Issue $issue,
        public string $comment,
        public array $attachments,
        public User $user,
    ) {
    }
}
