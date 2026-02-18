<?php

declare(strict_types=1);

namespace App\Message\Query\App\Project;

use App\Entity\Project;
use App\Entity\User;

class GetKanbanIssueByBoardId
{
    /**
     * @param array<string> $priorityJiraIds
     */
    public function __construct(
        public Project $project,
        public User $user,
        public string $boardId,
        public ?string $assigneeId = '',
        public array $priorityJiraIds = [],
        public ?string $sort = null,
    ) {
    }
}
