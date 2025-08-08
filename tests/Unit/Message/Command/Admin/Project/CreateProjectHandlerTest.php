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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Zenstruck\Foundry\Test\Factories;

class CreateProjectHandlerTest extends TestCase
{
    use Factories;

    private AppProjectRepository|MockObject $appProjectRepository;

    private ProjectRepository|MockObject $jiraProjectRepository;

    private EntityManagerInterface|MockObject $entityManager;

    private MessageBusInterface|MockObject $commandBus;

    protected function setUp(): void
    {
        $this->appProjectRepository = $this->createMock(AppProjectRepository::class);
        $this->jiraProjectRepository = $this->createMock(ProjectRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->commandBus = $this->createMock(MessageBusInterface::class);
    }

    #[Test]
    public function testItMustFailedIfProjectAlreadyExist(): void
    {
        $project = ProjectFactory::createOne([
            'jiraKey' => 'test',
        ]);

        $this->appProjectRepository
            ->expects($this->any())
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
            ->expects($this->any())
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
        $jiraProject = $this->createMock(Project::class);
        $jiraProject->name = 'test';
        $jiraProject->id = 12;
        $jiraProject->key = 'test';
        $jiraProject->description = 'test';

        $this->jiraProjectRepository
            ->expects($this->any())
            ->method('get')
            ->willReturn($jiraProject)
        ;

        $this->commandBus->expects(self::once())
            ->method('dispatch')
            ->with(
                self::isInstanceOf(GenerateProjectIssueTypes::class),
            )
            ->willReturn(new Envelope($this->createMock(GenerateProjectIssueTypes::class)))
        ;


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
