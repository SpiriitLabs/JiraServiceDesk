<?php

declare(strict_types=1);

namespace App\Formatter\Jira;

use App\Entity\Project;
use JiraCloud\Board\BoardColumnConfig;
use JiraCloud\Issue\Issue;

class IssueKanbanFormatter
{
    /**
     * @param array<Issue> $issues
     *
     * @return array<Issue>
     */
    public function format(array $issues, Project $project, ?BoardColumnConfig $columnsConfiguration = null): array
    {
        $firstIssue = $issues[0] ?? null;
        $result = $this->initResult($project, $columnsConfiguration, $firstIssue);

        foreach ($issues as $issue) {
            /** @var Issue $issue */
            $result = $this->insertIssueInResult(
                result: $result,
                issue: $issue,
                project: $project,
                columnsConfiguration: $columnsConfiguration
            );
        }

        return $result;
    }

    private function initResult(
        Project $project,
        ?BoardColumnConfig $columnsConfiguration = null,
        ?Issue $firstIssue = null
    ): array {
        $result = [];
        if ($columnsConfiguration === null) {
            return $result;
        }

        foreach ($columnsConfiguration->columns as $columnConfiguration) {
            $allBacklog = ! empty($columnConfiguration->statuses)
                && array_reduce(
                    $columnConfiguration->statuses,
                    fn ($carry, $status) => $carry && in_array($status->id, $project->backlogStatusesIds, true),
                    true
                );

            if (! $allBacklog) {
                $transitions = [];
                foreach ($columnConfiguration->statuses as $status) {
                    if ($firstIssue !== null) {
                        foreach ($firstIssue->transitions as $transition) {
                            if ($transition->to->id === $status->id) {
                                $transitions[] = [
                                    'id' => $transition->id,
                                    'name' => $transition->to->name,
                                ];
                            }
                        }
                    }
                }

                $result[$columnConfiguration->name] = [
                    'min' => $columnConfiguration->min,
                    'max' => $columnConfiguration->max,
                    'transitionIds' => $transitions,
                    'issues' => [],
                ];
            }
        }

        return $result;
    }

    /**
     * @param array<int,mixed> $result
     *
     * @return array|array<int,mixed>
     */
    private function insertIssueInResult(
        array $result,
        Issue $issue,
        Project $project,
        ?BoardColumnConfig $columnsConfiguration = null
    ): array {
        if (in_array($issue->fields->status->id, $project->backlogStatusesIds)) {
            return $result;
        }

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

    /**
     * @param array<int,mixed> $result
     *
     * @return array|array<int,mixed>
     */
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

    /**
     * @return array<int,mixed>|array<int,null>
     */
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
