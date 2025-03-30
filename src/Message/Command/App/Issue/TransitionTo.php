<?php

namespace App\Message\Command\App\Issue;

class TransitionTo
{
    public function __construct(
        public string $issueId,
        public string $transitionId,
    ) {
    }
}
