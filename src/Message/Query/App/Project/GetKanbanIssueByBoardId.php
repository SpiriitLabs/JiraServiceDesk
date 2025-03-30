<?php

namespace App\Message\Query\App\Project;

class GetKanbanIssueByBoardId
{
    public function __construct(
        public string $boardId,
    ) {
    }
}
