<?php

namespace App\Tests\Unit\Message\Command\App\Issue;

use App\Entity\IssueType;
use App\Entity\Priority;
use App\Factory\ProjectFactory;
use App\Message\Command\App\Issue\EditIssue;
use App\Message\Command\App\Issue\Handler\EditIssueHandler;
use App\Message\Command\App\Issue\TransitionTo;
use App\Repository\Jira\IssueRepository;
use JiraCloud\Issue\Issue;
use JiraCloud\Issue\IssueField;
use JiraCloud\Issue\IssueStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Zenstruck\Foundry\Test\Factories;

class EditIssueHandlerTest extends TestCase
{
    use Factories;

    private readonly MessageBusInterface|MockObject $commandBus;

    private readonly IssueRepository|MockObject $issueRepository;

    protected function setUp(): void
    {
        $this->commandBus = $this->createMock(MessageBusInterface::class);
        $this->issueRepository = $this->createMock(IssueRepository::class);
    }

    public static function emailNotificationDataProvider(): \Generator
    {
        yield 'update issue with change transition ' => [
            '20002',
            true,
        ];

        yield 'update issue without change transition ' => [
            '20001',
            false,
        ];
    }

    #[Test]
    #[DataProvider('emailNotificationDataProvider')]
    public function testUpdateIssue(
        string $issueStatus,
        bool $expectTransitionDispatch,
    ): void {
        $project = ProjectFactory::createOne([
            'jiraKey' => 'test',
        ]);

        $issueType = $this->createMock(IssueType::class);
        $issueType->jiraId = 10005;

        $priority = $this->createMock(Priority::class);
        $priority->jiraId = 10005;

        $transitionToStatus = (object) [
            'id' => '20001',
            'name' => 'Done',
        ];
        $transition = (object) [
            'id' => '30001',
            'to' => $transitionToStatus,
        ];

        $issue = $this->createMock(Issue::class);
        $issue->key = 'issueKey';
        $issue->id = 'issueId';
        $issue->fields = new IssueField();
        $issue->fields->status = new IssueStatus();
        $issue->fields->status->id = $issueStatus;
        $issue->fields->summary = 'Issue summary';
        $issue->transitions = [$transition];

        $this->issueRepository
            ->expects(self::once())
            ->method('update')
            ->willReturn('issueKey')
        ;

        $enveloppe = new Envelope(
            message: $this->createMock(TransitionTo::class),
            stamps: [new HandledStamp(result: $issue, handlerName: EditIssueHandler::class)],
        );
        $this->commandBus
            ->expects($expectTransitionDispatch ? self::once() : self::never())
            ->method('dispatch')
            ->willReturn($enveloppe)
        ;

        $handler = $this->generate();
        $handler(
            new EditIssue(
                project: $project,
                issue: $issue,
                issueType: $issueType,
                priority: $priority,
                transition: '30001',
                assignee: 'null',
            ),
        );
    }

    private function generate(): EditIssueHandler
    {
        $handler = new EditIssueHandler(
            issueRepository: $this->issueRepository,
        );
        $handler->setMessageBus($this->commandBus);

        return $handler;
    }
}
