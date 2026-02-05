<?php

namespace App\Tests\Unit\Message\Event\Webhook\Event;

use App\Entity\IssueLabel;
use App\Factory\ProjectFactory;
use App\Factory\UserFactory;
use App\Message\Command\Common\Notification;
use App\Message\Event\Webhook\Comment\CommentCreated;
use App\Message\Event\Webhook\Comment\Handler\CommentCreatedHandler;
use App\Repository\Jira\IssueRepository;
use App\Repository\ProjectRepository;
use App\Service\ReplaceAccountIdByDisplayName;
use JiraCloud\Issue\Comment;
use JiraCloud\Issue\Visibility;
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

class CommentCreatedHandlerTest extends TestCase
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
        yield 'notif CommentCreated and CommentOnlyOnTag none false ' => [
            false,
            false,
            'test',
            false,
        ];

        yield 'notif CommentCreated true and notif CommentOnlyOnTag false ' => [
            true,
            false,
            'test',
            true,
        ];

        yield 'notif CommentCreated false and notif CommentOnlyOnTag true and no tag in comment' => [
            false,
            true,
            'test',
            false,
        ];

        yield 'notif CommentCreated false and notif CommentOnlyOnTag true and tag in comment' => [
            false,
            true,
            '[~accountid:1234-5678]',
            true,
        ];
    }

    #[Test]
    public function testNoSendIfCommentPrivate(): void
    {
        $project = ProjectFactory::createOne([
            'jiraKey' => 'test',
        ]);

        $this->projectRepository
            ->method('findOneBy')
            ->willReturn($project)
        ;

        $comment = $this->createMock(Comment::class);
        $comment->visibility = new Visibility();
        $this->issueRepository
            ->method('getComment')
            ->willReturn($comment)
        ;

        $this->commandBus
            ->expects(self::never())
            ->method('dispatch')
            ->with(
                self::isInstanceOf(Notification::class),
            )
            ->willReturn(new Envelope($this->createMock(Notification::class)))
        ;

        $handler = $this->generate();
        $handler(
            new CommentCreated(
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
                        'author' => [
                            'avatarUrls' => [
                                'test',
                            ],
                        ],
                    ],
                ],
            ),
        );
    }

    #[Test]
    #[DataProvider('emailNotificationDataProvider')]
    public function testDoSendEmailNotification(
        bool $userHasPreferenceNotificationCommentCreated,
        bool $userHasPreferenceNotificationCommentOnlyOnTag,
        string $commentBody,
        bool $expectDispatch,
    ): void {
        $user = UserFactory::createOne([
            'email' => 'test@local.lan',
            'preferenceNotificationCommentCreated' => $userHasPreferenceNotificationCommentCreated,
            'preferenceNotificationCommentOnlyOnTag' => $userHasPreferenceNotificationCommentOnlyOnTag,
        ]);
        $label = new IssueLabel('from-client', 'from-client');
        $user->setIssueLabel($label);

        $project = ProjectFactory::createOne([
            'jiraKey' => 'test',
        ]);
        $user->addProject($project);

        $this->projectRepository
            ->method('findOneBy')
            ->willReturn($project)
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
            ->expects($expectDispatch ? self::once() : self::never())
            ->method('dispatch')
            ->willReturn(new Envelope($this->createMock(Notification::class)))
        ;

        $handler = $this->generate();
        $handler(
            new CommentCreated(
                payload: [
                    'issue' => [
                        'key' => 'issueKey',
                        'fields' => [
                            'summary' => 'summary',
                            'labels' => ['from-client'],
                            'project' => [
                                'id' => 'test',
                                'key' => 'test',
                            ],
                        ],
                    ],
                    'comment' => [
                        'id' => 'commentId',
                        'body' => $commentBody,
                        'author' => [
                            'avatarUrls' => [
                                'test',
                            ],
                        ],
                    ],
                ],
            ),
        );
    }

    public static function labelAndProjectFilteringDataProvider(): \Generator
    {
        yield 'user with matching label' => [
            'from-client',
            ['from-client'],
            true,
            true,
        ];

        yield 'user with matching label among multiple' => [
            'from-client',
            ['from-client', 'urgent'],
            true,
            true,
        ];

        yield 'user without label, issue with labels' => [
            null,
            ['from-client'],
            true,
            false,
        ];

        yield 'user with label, issue without labels' => [
            'from-client',
            [],
            true,
            false,
        ];

        yield 'user with matching label but not in project' => [
            'from-client',
            ['from-client'],
            false,
            false,
        ];
    }

    #[Test]
    #[DataProvider('labelAndProjectFilteringDataProvider')]
    public function testLabelAndProjectFiltering(
        ?string $userLabel,
        array $issueLabels,
        bool $userInProject,
        bool $expectDispatch,
    ): void {
        $user = UserFactory::createOne([
            'email' => 'test@local.lan',
            'preferenceNotificationCommentCreated' => true,
            'preferenceNotificationCommentOnlyOnTag' => false,
        ]);
        if ($userLabel !== null) {
            $label = new IssueLabel($userLabel, $userLabel);
            $user->setIssueLabel($label);
        }

        $project = ProjectFactory::createOne([
            'jiraKey' => 'test',
        ]);
        if ($userInProject) {
            $user->addProject($project);
        }

        $this->projectRepository
            ->method('findOneBy')
            ->willReturn($project)
        ;

        $comment = new Comment();
        $comment->id = 1;
        $comment->visibility = null;
        $this->issueRepository
            ->method('getComment')
            ->willReturn($comment)
        ;

        $this->commandBus
            ->expects($expectDispatch ? self::once() : self::never())
            ->method('dispatch')
            ->willReturn(new Envelope($this->createMock(Notification::class)))
        ;

        $handler = $this->generate();
        $handler(
            new CommentCreated(
                payload: [
                    'issue' => [
                        'key' => 'issueKey',
                        'fields' => [
                            'summary' => 'summary',
                            'labels' => $issueLabels,
                            'project' => [
                                'id' => 'test',
                                'key' => 'test',
                            ],
                        ],
                    ],
                    'comment' => [
                        'id' => 'commentId',
                        'body' => 'test',
                        'author' => [
                            'avatarUrls' => [
                                'test',
                            ],
                        ],
                    ],
                ],
            ),
        );
    }

    private function generate(): CommentCreatedHandler
    {
        $handler = new CommentCreatedHandler(
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
