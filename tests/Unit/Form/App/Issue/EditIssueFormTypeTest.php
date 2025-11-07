<?php

namespace App\Tests\Unit\Form\App\Issue;

use App\Entity\IssueType;
use App\Entity\Priority;
use App\Factory\ProjectFactory;
use App\Factory\UserFactory;
use App\Form\App\Issue\EditIssueFormType;
use App\Message\Command\App\Issue\EditIssue;
use App\Repository\IssueTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use JiraCloud\Issue\Issue;
use JiraCloud\Issue\IssueField;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bridge\Doctrine\Form\DoctrineOrmExtension;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;
use Zenstruck\Foundry\Test\Factories;

class EditIssueFormTypeTest extends TypeTestCase
{
    use Factories;

    private Security|MockObject $security;

    private IssueTypeRepository|MockObject $issueTypeRepository;

    protected function setUp(): void
    {
        $this->security = $this->createMock(Security::class);
        $this->issueTypeRepository = $this->createMock(IssueTypeRepository::class);

        parent::setUp();
    }

    protected function getExtensions(): array
    {
        $type = new EditIssueFormType($this->security);

        // Mock entity.
        $issueType = $this->createMock(IssueType::class);
        $issueType->method('getId')
            ->willReturn(25)
        ;

        // Mock repository
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder
            ->method('getParameters')
            ->willReturn(new ArrayCollection([]))
        ;
        $queryBuilder
            ->method('where')
            ->willReturn($queryBuilder)
        ;
        $queryBuilder
            ->method('setParameter')
            ->willReturn($queryBuilder)
        ;
        $query = $this->createMock(Query::class);
        $query
            ->method('getResult')
            ->willReturn([])
        ;
        $query
            ->method('execute')
            ->willReturn([$issueType])
        ;
        $queryBuilder
            ->method('getQuery')
            ->willReturn($query)
        ;

        $this->issueTypeRepository
            ->method('findBy')
            ->willReturn([$issueType])
        ;
        $this->issueTypeRepository
            ->method('createQueryBuilder')
            ->willReturn($queryBuilder)
        ;

        // Mock EntityManager
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')
            ->willReturn($this->issueTypeRepository)
        ;
        $em->method('getClassMetadata')
            ->willReturn($this->createMock(ClassMetadata::class))
        ;

        // Mock ManagerRegistry
        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $managerRegistry
            ->method('getManagerForClass')
            ->willReturn($em)
        ;

        $validator = Validation::createValidator();

        return [
            new PreloadedExtension([$type], []),
            new ValidatorExtension($validator),
            new DoctrineOrmExtension($managerRegistry),
        ];
    }

    #[Test]
    public function testSubmitValidData(): void
    {
        $formData = [
            'summary' => 'Issue summary - updated',
            'priority' => 'test',
            'transition' => '2001',
            'type' => 25,
        ];

        $project = ProjectFactory::createOne([
            'jiraKey' => 'test',
        ]);

        $user = UserFactory::createOne();

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
        $issue->fields->summary = 'Issue summary';
        $issue->transitions = [$transition];

        $issueTypes = $this->issueTypeRepository->findBy([]);
        $issueType = reset($issueTypes);

        $priority = $this->createMock(Priority::class);

        $model = new EditIssue(
            project: $project,
            issue: $issue,
            creator: $user,
            issueType: $issueType,
            priority: $priority,
            transition: $transition->id,
            assignee: 'null',
        );
        $form = $this->factory->create(
            type: EditIssueFormType::class,
            data: $model,
        );

        $issue->fields->summary = 'Issue summary - updated';
        $expected = new EditIssue(
            project: $project,
            issue: $issue,
            creator: $user,
            issueType: $issueType,
            priority: $priority,
            transition: $transition->id,
            assignee: 'null',
        );

        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expected, $model);
    }
}
