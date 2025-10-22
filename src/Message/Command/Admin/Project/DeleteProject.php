<?php

declare(strict_types=1);

namespace App\Message\Command\Admin\Project;

use App\Entity\Project;

class DeleteProject
{
    public function __construct(
        public Project $project,
    ) {
    }
}
