<?php

namespace App\Message\Query\App\Issue;

use App\Entity\Project;
use App\Entity\User;

class GetIssueAssignableUsers
{
    public function __construct(
        public User $user,
        public ?Project $project = null,
    ) {
    }
}
