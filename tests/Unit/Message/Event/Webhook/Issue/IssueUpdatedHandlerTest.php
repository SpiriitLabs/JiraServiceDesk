<?php

namespace App\Tests\Unit\Message\Event\Webhook\Issue;

use App\Factory\ProjectFactory;
use App\Factory\UserFactory;
use App\Formatter\Jira\IssueHistoryFormatter;
use App\Message\Command\App\Notification\CreateNotification;
use App\Message\Command\Common\EmailNotification;
use App\Message\Event\Webhook\Issue\Handler\IssueUpdatedHandler;
use App\Message\Event\Webhook\Issue\IssueUpdated;
use App\Repository\Jira\IssueRepository;
use App\Repository\ProjectRepository;
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

class IssueUpdatedHandlerTest extends TestCase
{
    use Factories;

    private readonly MessageBusInterface|MockObject $commandBus;

    private readonly ProjectRepository|MockObject $projectRepository;

    private readonly TranslatorInterface|MockObject $translator;

    private readonly IssueRepository|MockObject $issueRepository;

    private readonly IssueHistoryFormatter|MockObject $issueHistoryFormatter;

    private readonly RouterInterface|MockObject $router;

    protected function setUp(): void
    {
        $this->commandBus = $this->createMock(MessageBusInterface::class);
        $this->projectRepository = $this->createMock(ProjectRepository::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->issueRepository = $this->createMock(IssueRepository::class);
        $this->issueHistoryFormatter = $this->createMock(IssueHistoryFormatter::class);
        $this->router = $this->createMock(RouterInterface::class);
    }

    public static function emailNotificationDataProvider(): \Generator
    {
        yield 'can send notification' => [
            true,
        ];

        yield 'can\'t send notification' => [
            false,
        ];
    }

    #[Test]
    #[DataProvider('emailNotificationDataProvider')]
    public function testDoSendEmailNotification(bool $userHasPreferenceNotificationIssueUpdated): void
    {
        $user = UserFactory::createOne([
            'email' => 'test@local.lan',
            'preferenceNotificationIssueUpdated' => $userHasPreferenceNotificationIssueUpdated,
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

        $this->commandBus
            ->expects($userHasPreferenceNotificationIssueUpdated ? self::exactly(2) : self::never())
            ->method('dispatch')
            ->willReturnCallback(function ($command) {
                if ($command instanceof EmailNotification) {
                    return new Envelope($this->createMock(EmailNotification::class));
                }

                if ($command instanceof CreateNotification) {
                    return new Envelope($this->createMock(CreateNotification::class));
                }

                throw new \InvalidArgumentException('Unexpected command ' . get_class($command));
            })
        ;

        $handler = $this->generate();
        $handler(
            new IssueUpdated(
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
                ],
            ),
        );
    }

    private function generate(): IssueUpdatedHandler
    {
        $handler = new IssueUpdatedHandler(
            commandBus: $this->commandBus,
            projectRepository: $this->projectRepository,
            translator: $this->translator,
            issueRepository: $this->issueRepository,
            issueHistoryFormatter: $this->issueHistoryFormatter,
            router: $this->router,
        );
        $logger = $this->createMock(LoggerInterface::class);
        $handler->setLogger($logger);

        return $handler;
    }
}
