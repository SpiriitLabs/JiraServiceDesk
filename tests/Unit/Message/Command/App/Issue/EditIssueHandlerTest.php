<?php

namespace App\Tests\Unit\Message\Command\App\Issue;

use App\Entity\IssueType;
use App\Entity\Priority;
use App\Factory\IssueLabelFactory;
use App\Factory\ProjectFactory;
use App\Factory\UserFactory;
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
use PHPUnit\Framework\MockObject\Stub;
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

        $user = UserFactory::createOne();

        $issueType = $this->createStub(IssueType::class);
        $issueType->jiraId = '10005';

        $priority = $this->createStub(Priority::class);
        $priority->jiraId = 10005;

        $transitionToStatus = (object) [
            'id' => '20001',
            'name' => 'Done',
        ];
        $transition = (object) [
            'id' => '30001',
            'to' => $transitionToStatus,
        ];

        $issue = $this->createStub(Issue::class);
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
            message: $this->createStub(TransitionTo::class),
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
                creator: $user,
                issueType: $issueType,
                priority: $priority,
                transition: '30001',
                assignee: 'null',
            ),
        );
    }

    #[Test]
    public function testUpdateIssuePreservesExistingLabelsAndAddsCreatorLabel(): void
    {
        $project = ProjectFactory::createOne([
            'jiraKey' => 'test',
        ]);

        $user = UserFactory::createOne();
        IssueLabelFactory::createOne([
            'users' => [$user],
            'jiraLabel' => 'blootips',
            'name' => 'Blootips',
        ]);

        $issueType = $this->createStub(IssueType::class);
        $issueType->jiraId = '10005';

        $priority = $this->createStub(Priority::class);
        $priority->jiraId = 10005;

        $transitionToStatus = (object) [
            'id' => '20001',
            'name' => 'Done',
        ];
        $transition = (object) [
            'id' => '30001',
            'to' => $transitionToStatus,
        ];

        $issue = $this->createStub(Issue::class);
        $issue->key = 'issueKey';
        $issue->id = 'issueId';
        $issue->fields = new IssueField();
        $issue->fields->status = new IssueStatus();
        $issue->fields->status->id = '20001';
        $issue->fields->summary = 'Issue summary';
        $issue->fields->labels = ['from-client', 'example', 'outil-support'];
        $issue->transitions = [$transition];

        $capturedIssueField = null;
        $this->issueRepository
            ->expects(self::once())
            ->method('update')
            ->with(
                self::identicalTo($issue),
                self::callback(function (IssueField $issueField) use (&$capturedIssueField): bool {
                    $capturedIssueField = $issueField;

                    return true;
                }),
            )
            ->willReturn('issueKey')
        ;

        $enveloppe = new Envelope(
            message: $this->createStub(TransitionTo::class),
            stamps: [new HandledStamp(result: $issue, handlerName: EditIssueHandler::class)],
        );
        $this->commandBus
            ->expects(self::never())
            ->method('dispatch')
            ->willReturn($enveloppe)
        ;

        $handler = $this->generate();
        $handler(
            new EditIssue(
                project: $project,
                issue: $issue,
                creator: $user,
                issueType: $issueType,
                priority: $priority,
                transition: '30001',
                assignee: 'null',
            ),
        );

        // Verify that all existing labels are preserved and creator's label is added
        self::assertNotNull($capturedIssueField);
        $labels = $capturedIssueField->labels;
        self::assertContains('from-client', $labels);
        self::assertContains('example', $labels);
        self::assertContains('outil-support', $labels);
        self::assertContains('blootips', $labels);
        self::assertCount(4, $labels);
    }

    #[Test]
    public function testUpdateIssueDoesNotDuplicateCreatorLabelWhenAlreadyPresent(): void
    {
        $project = ProjectFactory::createOne([
            'jiraKey' => 'test',
        ]);

        $user = UserFactory::createOne();
        IssueLabelFactory::createOne([
            'users' => [$user],
            'jiraLabel' => 'blootips',
            'name' => 'Blootips',
        ]);

        $issueType = $this->createStub(IssueType::class);
        $issueType->jiraId = '10005';

        $priority = $this->createStub(Priority::class);
        $priority->jiraId = 10005;

        $transitionToStatus = (object) [
            'id' => '20001',
            'name' => 'Done',
        ];
        $transition = (object) [
            'id' => '30001',
            'to' => $transitionToStatus,
        ];

        $issue = $this->createStub(Issue::class);
        $issue->key = 'issueKey';
        $issue->id = 'issueId';
        $issue->fields = new IssueField();
        $issue->fields->status = new IssueStatus();
        $issue->fields->status->id = '20001';
        $issue->fields->summary = 'Issue summary';
        // Creator's label 'blootips' is already in the existing labels
        $issue->fields->labels = ['from-client', 'example', 'blootips'];
        $issue->transitions = [$transition];

        $capturedIssueField = null;
        $this->issueRepository
            ->expects(self::once())
            ->method('update')
            ->with(
                self::identicalTo($issue),
                self::callback(function (IssueField $issueField) use (&$capturedIssueField): bool {
                    $capturedIssueField = $issueField;

                    return true;
                }),
            )
            ->willReturn('issueKey')
        ;

        $enveloppe = new Envelope(
            message: $this->createStub(TransitionTo::class),
            stamps: [new HandledStamp(result: $issue, handlerName: EditIssueHandler::class)],
        );
        $this->commandBus
            ->expects(self::never())
            ->method('dispatch')
            ->willReturn($enveloppe)
        ;

        $handler = $this->generate();
        $handler(
            new EditIssue(
                project: $project,
                issue: $issue,
                creator: $user,
                issueType: $issueType,
                priority: $priority,
                transition: '30001',
                assignee: 'null',
            ),
        );

        // Verify that labels are preserved and creator's label is not duplicated
        self::assertNotNull($capturedIssueField);
        $labels = $capturedIssueField->labels;
        self::assertContains('from-client', $labels);
        self::assertContains('example', $labels);
        self::assertContains('blootips', $labels);
        self::assertCount(3, $labels); // No duplicate 'blootips'
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
