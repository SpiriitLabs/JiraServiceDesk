<?php

namespace App\Formatter\Jira;

use JiraCloud\Issue\Issue;

class IssueHistoryFormatter
{
    public function format(Issue $issue): array
    {
        $history = $issue->changelog->histories;
        $changes = [
            'assignee' => [],
            'status' => [],
            'description' => [],
        ];
        foreach ($history as $item) {
            $fiveMinutesAgo = time() - 300;
            if (
                strtotime($item->created) < $fiveMinutesAgo
                || ! isset($changes[$item->items[0]->field])
            ) {
                continue;
            }
            $changes[$item->items[0]->field][] = [
                'from' => $item->items[0]->fromString,
                'to' => $item->items[0]->toString,
            ];
        }

        return $changes;
    }
}
