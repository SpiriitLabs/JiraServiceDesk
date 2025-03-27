<?php

namespace App\Formatter\Jira;

use JiraCloud\Issue\Issue;

class IssueKanbanFormatter
{

    /**
     * @param array<Issue> $issues
     * @return array<Issue>
     */
    public function format(array $issues): array
    {
        $result = [];

        foreach ($issues as $issue) {
            /** @var Issue $issue */

            if (!isset($result[$issue->fields->status->id])) {
                $result[$issue->fields->status->id] = [
                    'name' => $issue->fields->status->name,
                    'description' => $issue->fields->status->description,
                    'id' => $issue->fields->status->id,
                    'issues' => [],
                ];
            }

            $result[$issue->fields->status->id]['issues'][] = $issue;
        }

        return $result;
    }

}
