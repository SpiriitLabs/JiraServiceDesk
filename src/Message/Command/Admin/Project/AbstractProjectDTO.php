<?php

namespace App\Message\Command\Admin\Project;

class AbstractProjectDTO
{
    public function __construct(
        public string $jiraKey = '',
        public array $users = [],
    ) {
    }
}
