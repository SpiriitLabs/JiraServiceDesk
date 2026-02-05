<?php

namespace App\Tests\Unit\Message\Event\Webhook\Issue;

use App\Entity\IssueLabel;
use App\Factory\ProjectFactory;
use App\Factory\UserFactory;
use App\Message\Command\Common\Notification;
use App\Message\Event\Webhook\Issue\Handler\IssueDeletedHandler;
use App\Message\Event\Webhook\Issue\IssueDeleted;
use App\Repository\FavoriteRepository;
use App\Repository\ProjectRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
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

class IssueDeletedHandlerTest extends TestCase
{
    use Factories;

    private readonly MessageBusInterface|MockObject $commandBus;

    private readonly ProjectRepository|MockObject $projectRepository;

    private readonly TranslatorInterface|MockObject $translator;

    private readonly FavoriteRepository|MockObject $favoriteRepository;

    private readonly RouterInterface|MockObject $router;

    protected function setUp(): void
    {
        $this->commandBus = $this->createMock(MessageBusInterface::class);
        $this->projectRepository = $this->createMock(ProjectRepository::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->favoriteRepository = $this->createMock(FavoriteRepository::class);
        $this->router = $this->createMock(RouterInterface::class);

        $query = $this->createMock(Query::class);
        $query->method('getResult')->willReturn([]);

        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('where')->willReturn($queryBuilder);
        $queryBuilder->method('setParameter')->willReturn($queryBuilder);
        $queryBuilder->method('getQuery')->willReturn($query);

        $this->favoriteRepository
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder)
        ;
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
            'preferenceNotificationIssueUpdated' => true,
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

        $this->commandBus
            ->expects($expectDispatch ? self::once() : self::never())
            ->method('dispatch')
            ->willReturn(new Envelope($this->createMock(Notification::class)))
        ;

        $handler = $this->generate();
        $handler(
            new IssueDeleted(
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

    private function generate(): IssueDeletedHandler
    {
        $handler = new IssueDeletedHandler(
            commandBus: $this->commandBus,
            projectRepository: $this->projectRepository,
            translator: $this->translator,
            favoriteRepository: $this->favoriteRepository,
            router: $this->router,
        );
        $logger = $this->createMock(LoggerInterface::class);
        $handler->setLogger($logger);

        return $handler;
    }
}
