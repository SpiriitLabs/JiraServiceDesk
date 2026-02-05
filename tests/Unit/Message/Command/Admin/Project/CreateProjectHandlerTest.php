<?php

namespace App\Tests\Unit\Message\Command\Admin\Project;

use App\Entity\Project as AppProject;
use App\Exception\Project\ProjectAlreadyExistException;
use App\Factory\ProjectFactory;
use App\Message\Command\Admin\Project\CreateProject;
use App\Message\Command\Admin\Project\GenerateProjectIssueTypes;
use App\Message\Command\Admin\Project\Handler\CreateProjectHandler;
use App\Repository\Jira\ProjectRepository;
use App\Repository\ProjectRepository as AppProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use JiraCloud\Project\Project;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Zenstruck\Foundry\Test\Factories;

class CreateProjectHandlerTest extends TestCase
{
    use Factories;

    private AppProjectRepository|Stub $appProjectRepository;

    private ProjectRepository|Stub $jiraProjectRepository;

    private EntityManagerInterface|Stub $entityManager;

    private MessageBusInterface|Stub $commandBus;

    protected function setUp(): void
    {
        $this->appProjectRepository = $this->createStub(AppProjectRepository::class);
        $this->jiraProjectRepository = $this->createStub(ProjectRepository::class);
        $this->entityManager = $this->createStub(EntityManagerInterface::class);
        $this->commandBus = $this->createStub(MessageBusInterface::class);
    }

    #[Test]
    public function testItMustFailedIfProjectAlreadyExist(): void
    {
        $project = ProjectFactory::createOne([
            'jiraKey' => 'test',
        ]);

        $this->appProjectRepository
            ->method('findOneBy')
            ->willReturnMap([
                [
                    [
                        'jiraKey' => 'test',
                    ],
                    $project,
                ],
            ])
        ;

        $this->expectException(ProjectAlreadyExistException::class);

        $handler = $this->generate();
        $handler(
            new CreateProject(
                jiraKey: 'test',
            ),
        );
    }

    #[Test]
    public function testItReturnNullIfNoProject(): void
    {
        $this->jiraProjectRepository
            ->method('get')
            ->willReturn(null)
        ;

        $handler = $this->generate();
        $project = $handler(
            new CreateProject(
                jiraKey: 'test',
            ),
        );

        $this->assertNull($project);
    }

    #[Test]
    public function testItMustReturnProject(): void
    {
        $jiraProject = $this->createStub(Project::class);
        $jiraProject->name = 'test';
        $jiraProject->id = 12;
        $jiraProject->key = 'test';
        $jiraProject->description = 'test';

        $this->jiraProjectRepository
            ->method('get')
            ->willReturn($jiraProject)
        ;

        $commandBus = $this->createMock(MessageBusInterface::class);
        $commandBus->expects(self::once())
            ->method('dispatch')
            ->with(
                self::isInstanceOf(GenerateProjectIssueTypes::class),
            )
            ->willReturn(new Envelope($this->createStub(GenerateProjectIssueTypes::class)))
        ;
        $this->commandBus = $commandBus;


        $handler = $this->generate();
        $project = $handler(
            new CreateProject(
                jiraKey: 'test',
            ),
        );

        $this->assertInstanceOf(AppProject::class, $project);
    }

    private function generate(): CreateProjectHandler
    {
        return new CreateProjectHandler(
            appProjectRepository: $this->appProjectRepository,
            jiraProjectRepository: $this->jiraProjectRepository,
            entityManager: $this->entityManager,
            commandBus: $this->commandBus
        );
    }
}
