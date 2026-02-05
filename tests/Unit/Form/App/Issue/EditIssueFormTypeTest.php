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
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub;
use Symfony\Bridge\Doctrine\Form\DoctrineOrmExtension;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;
use Zenstruck\Foundry\Test\Factories;

#[AllowMockObjectsWithoutExpectations]
class EditIssueFormTypeTest extends TypeTestCase
{
    use Factories;

    private Security|Stub $security;

    private IssueTypeRepository|Stub $issueTypeRepository;

    protected function setUp(): void
    {
        $this->security = $this->createStub(Security::class);
        $this->issueTypeRepository = $this->createStub(IssueTypeRepository::class);

        parent::setUp();
    }

    protected function getExtensions(): array
    {
        $type = new EditIssueFormType($this->security);

        // Mock entity.
        $issueType = $this->createStub(IssueType::class);
        $issueType->method('getId')
            ->willReturn(25)
        ;

        // Mock repository
        $queryBuilder = $this->createStub(QueryBuilder::class);
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
        $query = $this->createStub(Query::class);
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
        $em = $this->createStub(EntityManagerInterface::class);
        $em->method('getRepository')
            ->willReturn($this->issueTypeRepository)
        ;
        $em->method('getClassMetadata')
            ->willReturn($this->createStub(ClassMetadata::class))
        ;

        // Mock ManagerRegistry
        $managerRegistry = $this->createStub(ManagerRegistry::class);
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
        $issue = $this->createStub(Issue::class);
        $issue->key = 'issueKey';
        $issue->id = 'issueId';
        $issue->fields = new IssueField();
        $issue->fields->summary = 'Issue summary';
        $issue->transitions = [$transition];

        $issueTypes = $this->issueTypeRepository->findBy([]);
        $issueType = reset($issueTypes);

        $priority = $this->createStub(Priority::class);

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
