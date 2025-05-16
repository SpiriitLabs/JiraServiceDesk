<?php

namespace App\Message\Command\Admin\Project;

use App\Entity\Project;

class EditProject extends AbstractProjectDTO
{
    public function __construct(
        public Project $project,
        public array $assignableRolesIds = [],
        public array $backlogStatuses = [],
    ) {
        parent::__construct(
            jiraKey: $this->project->jiraKey,
            users: $this->project->getUsers()
                ->toArray(),
        );

        $this->assignableRolesIds = $this->project->assignableRolesIds;
    }
}
