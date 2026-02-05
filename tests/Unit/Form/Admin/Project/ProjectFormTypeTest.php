<?php

namespace App\Tests\Unit\Form\Admin\Project;

use App\Form\Admin\Project\ProjectFormType;
use App\Message\Command\Admin\Project\CreateProject;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bridge\Doctrine\Form\DoctrineOrmExtension;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\Autocomplete\Checksum\ChecksumCalculator;
use Symfony\UX\Autocomplete\Form\AutocompleteChoiceTypeExtension;
use Zenstruck\Foundry\Test\Factories;

#[AllowMockObjectsWithoutExpectations]
class ProjectFormTypeTest extends TypeTestCase
{
    use Factories;

    protected function getExtensions(): array
    {
        $validator = Validation::createValidator();
        $checksumCalculator = $this->createStub(ChecksumCalculator::class);
        $translator = $this->createStub(TranslatorInterface::class);

        // Mock EntityManager
        $entityManager = $this->createStub(EntityManagerInterface::class);

        // Mock ManagerRegistry
        $managerRegistry = $this->createStub(ManagerRegistry::class);
        $managerRegistry->method('getManagerForClass')
            ->willReturn($entityManager)
        ;

        return [
            new PreloadedExtension(
                types: [
                    new ProjectFormType(),
                ],
                typeExtensions: [
                    EntityType::class => [
                        new AutocompleteChoiceTypeExtension($checksumCalculator, $translator),
                    ],
                ]
            ),
            new ValidatorExtension($validator),
            new DoctrineOrmExtension($managerRegistry),
        ];
    }

    #[Test]
    public function testSubmitValidDataOnCreateProject(): void
    {
        $formData = [
            'jiraKey' => 'jiraKey',
            'users' => [],
        ];

        $model = new CreateProject();

        $form = $this->factory->create(
            type: ProjectFormType::class,
            data: $model,
        );

        $expected = new CreateProject(
            jiraKey: 'jiraKey',
            users: [],
        );

        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expected, $model);
    }
}
