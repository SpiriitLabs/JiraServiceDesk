<?php

namespace App\Tests\Unit\Formatter\Jira;

use App\Formatter\Jira\IssueHistoryFormatter;
use JiraCloud\Issue\ChangeLog;
use JiraCloud\Issue\History;
use JiraCloud\Issue\Issue;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IssueHistoryTest extends TestCase
{
    private IssueHistoryFormatter|MockObject $formatter;

    protected function setUp(): void
    {
        $this->formatter = new IssueHistoryFormatter();
    }

    #[Test]
    public function testFormatReturnsEmptyArrayWhenNoColumnsConfiguration(): void
    {
        $histories = [];
        for ($i = 0; $i < 5; ++$i) {
            $item = (object) [
                'field' => 'description',
                'fromString' => 'from',
                'toString' => 'to',
            ];
            $history = new History();
            $history->created = date('Y-m-d\TH:i:s\.uO');
            $history->items = [$item];
            $histories[] = $history;
        }

        $issue = $this->createMock(Issue::class);
        $issue->changelog = new ChangeLog();
        $issue->changelog->histories = $histories;

        $result = $this->formatter->format($issue);

        $expected = [
            'assignee' => [],
            'status' => [],
            'description' => [
                [
                    'from' => 'from',
                    'to' => 'to',
                ],
                [
                    'from' => 'from',
                    'to' => 'to',
                ],
                [
                    'from' => 'from',
                    'to' => 'to',
                ], [
                    'from' => 'from',
                    'to' => 'to',
                ],
                [
                    'from' => 'from',
                    'to' => 'to',
                ],
            ],
        ];

        $this->assertEquals($expected, $result);
    }
}
