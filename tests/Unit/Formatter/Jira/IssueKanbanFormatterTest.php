<?php

namespace App\Tests\Unit\Formatter\Jira;

use App\Entity\Project;
use App\Formatter\Jira\IssueKanbanFormatter;
use JiraCloud\Board\BoardColumn;
use JiraCloud\Board\BoardColumnConfig;
use JiraCloud\Issue\Issue;
use JiraCloud\Issue\IssueField;
use JiraCloud\Issue\IssueStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IssueKanbanFormatterTest extends TestCase
{

    private IssueKanbanFormatter|MockObject $formatter;
    private Project $project;

    protected function setUp(): void
    {
        $this->formatter = new IssueKanbanFormatter();
        $this->project = $this->createMock(Project::class);
        $this->project->backlogStatusesIds = ['1000'];
    }

    #[Test]
    public function testFormatReturnsEmptyArrayWhenNoColumnsConfiguration(): void
    {
        $issue = $this->createMock(Issue::class);
        $issue->transitions = [];
        $issue->fields = new IssueField();
        $issue->fields->status = new IssueStatus();
        $issue->fields->status->id = '10001';
        $issue->fields->status->name = 'In Progress';

        $result = $this->formatter->format([$issue], $this->project, null);

        $expected = [
            'In Progress' => [
                'min' => 0,
                'max' => 0,
                'transitionId' => null,
                'issues' => [$issue],
            ],
        ];

        $this->assertEquals($expected, $result);
    }


    #[Test]
    public function testFormatWithColumnsConfiguration(): void
    {
        $transitionToStatus = (object)['id' => '20001', 'name' => 'Done'];
        $transition = (object)['id' => '30001', 'to' => $transitionToStatus];

        $issue = $this->createMock(Issue::class);
        $issue->transitions = [$transition];
        $issue->fields = new IssueField();
        $issue->fields->status = new IssueStatus();
        $issue->fields->status->id = '20001';
        $issue->fields->status->name = 'Done';

        $status = (object)['id' => '20001'];
        $columnConfig = $this->createMock(BoardColumn::class);
        $columnConfig->statuses = [$status];
        $columnConfig->min = 1;
        $columnConfig->max = 5;
        $columnConfig->name = 'Done';

        $columnsConfig = $this->createMock(BoardColumnConfig::class);
        $columnsConfig->columns = [$columnConfig];

        $result = $this->formatter->format([$issue], $this->project, $columnsConfig);

        $this->assertArrayHasKey('Done', $result);
        $this->assertIsArray($result['Done']['issues']);
        $this->assertCount(1, $result['Done']['issues']);
        $this->assertSame($issue, $result['Done']['issues'][0]);
        $this->assertArrayHasKey('transitionIds', $result['Done']);
        $this->assertIsArray($result['Done']['transitionIds']);
    }

    #[Test]
    public function testFormatSkipsBacklogStatusIssues(): void
    {
        $issue = $this->createMock(Issue::class);
        $issue->fields = new IssueField();
        $issue->fields->status = new IssueStatus();
        $issue->fields->status->id = '10000';
        $issue->fields->status->name = 'Backlog';
        $issue->transitions = [];

        $result = $this->formatter->format([$issue], $this->project, null);
        $this->assertSame([], $result);
    }

}
