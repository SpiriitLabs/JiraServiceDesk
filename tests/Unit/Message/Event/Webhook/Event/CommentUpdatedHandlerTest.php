<?php

namespace App\Tests\Unit\Message\Event\Webhook\Event;

use App\Factory\ProjectFactory;
use App\Factory\UserFactory;
use App\Message\Command\App\Notification\CreateNotification;
use App\Message\Command\Common\Notification;
use App\Message\Event\Webhook\Comment\CommentUpdated;
use App\Message\Event\Webhook\Comment\Handler\CommentUpdatedHandler;
use App\Repository\Jira\IssueRepository;
use App\Repository\ProjectRepository;
use App\Service\ReplaceAccountIdByDisplayName;
use JiraCloud\Issue\Comment;
use JiraCloud\Issue\Issue;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;

class CommentUpdatedHandlerTest extends TestCase
{
    use Factories;

    private readonly MessageBusInterface|MockObject $commandBus;

    private readonly ProjectRepository|MockObject $projectRepository;

    private readonly TranslatorInterface|MockObject $translator;

    private readonly IssueRepository|MockObject $issueRepository;

    private readonly ReplaceAccountIdByDisplayName|MockObject $replaceAccountIdByDisplayName;

    private readonly RouterInterface|MockObject $router;

    protected function setUp(): void
    {
        $this->commandBus = $this->createMock(MessageBusInterface::class);
        $this->projectRepository = $this->createMock(ProjectRepository::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->issueRepository = $this->createMock(IssueRepository::class);
        $this->replaceAccountIdByDisplayName = $this->createMock(ReplaceAccountIdByDisplayName::class);
        $this->router = $this->createMock(RouterInterface::class);
    }

    public static function emailNotificationDataProvider(): \Generator
    {
        yield 'notif CommentUpdated and CommentOnlyOnTag none false ' => [
            false,
            false,
            'test',
            false,
        ];

        yield 'notif CommentUpdated true and notif CommentOnlyOnTag false ' => [
            true,
            false,
            'test',
            true,
        ];

        yield 'notif CommentUpdated false and notif CommentOnlyOnTag true and no tag in comment' => [
            false,
            true,
            'test',
            false,
        ];

        yield 'notif CommentUpdated false and notif CommentOnlyOnTag true and tag in comment' => [
            false,
            true,
            '[~accountid:1234-5678]',
            true,
        ];
    }

    #[Test]
    #[DataProvider('emailNotificationDataProvider')]
    public function testDoSendEmailNotification(
        bool $userHasPreferenceNotificationCommentUpdated,
        bool $userHasPreferenceNotificationCommentOnlyOnTag,
        string $commentBody,
        bool $expectDispatch,
    ): void {
        $user = UserFactory::createOne([
            'email' => 'test@local.lan',
            'preferenceNotificationCommentUpdated' => $userHasPreferenceNotificationCommentUpdated,
            'preferenceNotificationCommentOnlyOnTag' => $userHasPreferenceNotificationCommentOnlyOnTag,
        ]);

        $project = ProjectFactory::createOne([
            'jiraKey' => 'test',
        ]);
        $user->addProject($project);

        $this->projectRepository
            ->method('findOneBy')
            ->willReturn($project)
        ;

        $issue = $this->createMock(Issue::class);
        $this->issueRepository
            ->method('getFull')
            ->with('issueKey')
            ->willReturn($issue)
        ;

        $comment = new Comment();
        $comment->id = 1;
        $comment->visibility = null;
        $this->issueRepository
            ->method('getComment')
            ->with('issueKey', 'commentId')
            ->willReturn($comment)
        ;

        $this->commandBus
            ->expects($expectDispatch ? self::exactly(2) : self::never())
            ->method('dispatch')
            ->willReturnCallback(function ($command) {
                if ($command instanceof Notification) {
                    return new Envelope($this->createMock(Notification::class));
                }

                if ($command instanceof CreateNotification) {
                    return new Envelope($this->createMock(CreateNotification::class));
                }

                throw new \InvalidArgumentException('Unexpected command ' . get_class($command));
            })
        ;

        $handler = $this->generate();
        $handler(
            new CommentUpdated(
                payload: [
                    'issue' => [
                        'key' => 'issueKey',
                        'fields' => [
                            'summary' => 'summary',
                            'project' => [
                                'id' => 'test',
                                'key' => 'test',
                            ],
                        ],
                    ],
                    'comment' => [
                        'id' => 'commentId',
                        'body' => $commentBody,
                        'updateAuthor' => [
                            'avatarUrls' => [
                                'test',
                            ],
                        ],
                    ],
                ],
            ),
        );
    }

    private function generate(): CommentUpdatedHandler
    {
        $handler = new CommentUpdatedHandler(
            commandBus: $this->commandBus,
            projectRepository: $this->projectRepository,
            translator: $this->translator,
            issueRepository: $this->issueRepository,
            replaceAccountIdByDisplayName: $this->replaceAccountIdByDisplayName,
            jiraAPIAccountId: '1234-5678',
            router: $this->router,
        );
        $logger = $this->createMock(LoggerInterface::class);
        $handler->setLogger($logger);

        return $handler;
    }
}
