<?php

declare(strict_types=1);

namespace App\Message\Command\Admin\Project;

class AbstractProjectDTO
{
    public function __construct(
        public string $jiraKey = '',
        public array $users = [],
    ) {
    }
}
