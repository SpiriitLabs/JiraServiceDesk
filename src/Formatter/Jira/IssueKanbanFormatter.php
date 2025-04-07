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
                'transitionId' => null,
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
            return $this->insertWithoutColumnConfig($result, $issue);
        }
        [$issueColumnName, $issueStatusId] = $this->findColumnAndStatusId($columnsConfiguration, $issue);

        if ($issueColumnName === null) {
            return $result;
        }

        $result[$issueColumnName]['issues'][] = $issue;

        if (
            $issueStatusId !== null
            && ! isset($result[$issueColumnName]['transitionId'])
        ) {
            $result[$issueColumnName]['transitionId'] = $this->findTransitionId($issue, $issueStatusId);
        }

        return $result;
    }

    private function insertWithoutColumnConfig(array $result, Issue $issue): array
    {
        $statusName = $issue->fields->status->name;

        if (! isset($result[$statusName])) {
            $result[$statusName] = [
                'min' => 0,
                'max' => 0,
                'transitionId' => null,
                'issues' => [],
            ];
        }

        $result[$statusName]['issues'][] = $issue;

        return $result;
    }

    private function findColumnAndStatusId(BoardColumnConfig $config, Issue $issue): array
    {
        foreach ($config->columns as $column) {
            foreach ($column->statuses as $status) {
                if ($issue->fields->status->id === $status->id) {
                    return [$column->name, $status->id];
                }
            }
        }

        return [null, null];
    }

    private function findTransitionId(Issue $issue, string $targetStatusId): ?string
    {
        foreach ($issue->transitions as $transition) {
            if ($transition->to->id === $targetStatusId) {
                return $transition->id;
            }
        }

        return null;
    }
}
