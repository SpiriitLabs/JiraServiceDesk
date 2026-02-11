<?php

namespace App\Tests\Unit\Message\Event\Webhook\Issue;

use App\Entity\IssueLabel;
use App\Enum\Notification\NotificationChannel;
use App\Factory\ProjectFactory;
use App\Factory\UserFactory;
use App\Message\Command\Common\Notification;
use App\Message\Event\Webhook\Issue\Handler\IssueCreatedHandler;
use App\Message\Event\Webhook\Issue\IssueCreated;
use App\Repository\Jira\IssueRepository;
use App\Repository\ProjectRepository;
use JiraCloud\Issue\Issue;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;

class IssueCreatedHandlerTest extends TestCase
{
    use Factories;

    private readonly MessageBusInterface|MockObject $commandBus;

    private readonly ProjectRepository|Stub $projectRepository;

    private readonly TranslatorInterface|Stub $translator;

    private readonly IssueRepository|Stub $issueRepository;

    private readonly RouterInterface|Stub $router;

    protected function setUp(): void
    {
        $this->commandBus = $this->createMock(MessageBusInterface::class);
        $this->projectRepository = $this->createStub(ProjectRepository::class);
        $this->translator = $this->createStub(TranslatorInterface::class);
        $this->issueRepository = $this->createStub(IssueRepository::class);
        $this->router = $this->createStub(RouterInterface::class);
    }

    public static function emailNotificationDataProvider(): \Generator
    {
        yield 'can send notification' => [
            [NotificationChannel::IN_APP, NotificationChannel::EMAIL],
            true,
        ];

        yield 'can\'t send notification' => [
            [],
            false,
        ];
    }

    #[Test]
    #[DataProvider('emailNotificationDataProvider')]
    public function testDoSendEmailNotification(array $channels, bool $expectDispatch): void
    {
        $user = UserFactory::createOne([
            'email' => 'test@local.lan',
            'preferenceNotificationIssueCreated' => $channels,
        ]);
        $label = new IssueLabel('from-client', 'from-client');
        $user->addIssueLabel($label);

        $project = ProjectFactory::createOne([
            'jiraKey' => 'test',
        ]);
        $user->addProject($project);

        $this->projectRepository
            ->method('findOneBy')
            ->willReturn($project)
        ;

        $issue = $this->createStub(Issue::class);
        $this->issueRepository
            ->method('getFull')
            ->willReturn($issue)
        ;

        $this->commandBus
            ->expects($expectDispatch ? self::once() : self::never())
            ->method('dispatch')
            ->willReturn(new Envelope($this->createStub(Notification::class)))
        ;

        $handler = $this->generate();
        $handler(
            new IssueCreated(
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
            'preferenceNotificationIssueCreated' => [NotificationChannel::IN_APP, NotificationChannel::EMAIL],
        ]);
        if ($userLabel !== null) {
            $label = new IssueLabel($userLabel, $userLabel);
            $user->addIssueLabel($label);
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

        $issue = $this->createStub(Issue::class);
        $this->issueRepository
            ->method('getFull')
            ->willReturn($issue)
        ;

        $this->commandBus
            ->expects($expectDispatch ? self::once() : self::never())
            ->method('dispatch')
            ->willReturn(new Envelope($this->createStub(Notification::class)))
        ;

        $handler = $this->generate();
        $handler(
            new IssueCreated(
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
                ],
            ),
        );
    }

    private function generate(): IssueCreatedHandler
    {
        $handler = new IssueCreatedHandler(
            commandBus: $this->commandBus,
            projectRepository: $this->projectRepository,
            translator: $this->translator,
            issueRepository: $this->issueRepository,
            router: $this->router,
        );
        $logger = $this->createStub(LoggerInterface::class);
        $handler->setLogger($logger);

        return $handler;
    }
}
