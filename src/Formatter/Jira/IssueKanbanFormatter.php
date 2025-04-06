<?php

namespace App\Formatter\Jira;

use JiraCloud\Board\BoardColumnConfig;
use JiraCloud\Issue\Issue;

class IssueKanbanFormatter
{
    /**
     * @param array<Issue> $issues
     *
     * @return array<Issue>
     */
    public function format(array $issues, ?BoardColumnConfig $columnsConfiguration = null): array
    {
        $result = [
            'À faire' => [
                'issues' => [],
                'min' => 0,
                'max' => 0,
            ],
        ];
        $result = $this->formatResult($columnsConfiguration);

        foreach ($issues as $issue) {
            /** @var Issue $issue */
            $result = $this->insertIssueInResult(
                result: $result,
                issue: $issue,
                columnsConfiguration: $columnsConfiguration
            );
        }

        return $result;
    }

    private function formatResult(?BoardColumnConfig $columnsConfiguration = null): array
    {
        $result = [];
        if ($columnsConfiguration === null) {
            return $result;
        }

        foreach ($columnsConfiguration->columns as $columnConfiguration) {
            $result[$columnConfiguration->name] = [
                'min' => $columnConfiguration->min,
                'max' => $columnConfiguration->max,
                'issues' => [],
            ];
        }

        return $result;
    }

    private function insertIssueInResult(
        array $result,
        Issue $issue,
        ?BoardColumnConfig $columnsConfiguration = null
    ): array {
        if ($columnsConfiguration === null) {
            if (! isset($result[$issue->fields->status->name])) {
                $result[$issue->fields->status->name] = [
                    'min' => 0,
                    'max' => 0,
                    'issues' => [],
                ];
            }

            $result[$issue->fields->status->name]['issues'][] = $issue;

            return $result;
        }

        $issueColumnName = null;
        foreach ($columnsConfiguration->columns as $columnConfiguration) {
            foreach ($columnConfiguration->statuses as $statusConfiguration) {
                if ($issue->fields->status->id === $statusConfiguration->id) {
                    $issueColumnName = $columnConfiguration->name;
                    break;
                }
            }
        }

        if ($issueColumnName !== null) {
            $result[$issueColumnName]['issues'][] = $issue;
        }

        return $result;
    }
}
