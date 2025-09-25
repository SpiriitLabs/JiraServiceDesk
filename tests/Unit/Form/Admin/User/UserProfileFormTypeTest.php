<?php

namespace App\Tests\Unit\Form\Admin\User;

use App\Enum\User\Locale;
use App\Enum\User\Theme;
use App\Factory\UserFactory;
use App\Form\Admin\User\UserProfileFormType;
use App\Message\Command\User\EditUser;
use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bridge\Doctrine\Form\DoctrineOrmExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zenstruck\Foundry\Test\Factories;

class UserProfileFormTypeTest extends TypeTestCase
{
    use Factories;

    protected function getExtensions(): array
    {
        $validator = Validation::createValidator();
        $translator = $this->createMock(TranslatorInterface::class);

        // Mock ProjectRepository
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder
            ->method('getParameters')
            ->willReturn(new ArrayCollection([]))
        ;
        $query = $this->createMock(Query::class);
        $query
            ->method('execute')
            ->willReturn([])
        ;
        $queryBuilder
            ->method('getQuery')
            ->willReturn($query)
        ;

        $projectRepository = $this->createMock(ProjectRepository::class);
        $projectRepository->method('getByUser')
            ->willReturn($queryBuilder)
        ;

        // Mock EntityManager
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')
            ->willReturn($projectRepository)
        ;

        // Mock ManagerRegistry
        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $managerRegistry->method('getManagerForClass')
            ->willReturn($em)
        ;

        return [
            new PreloadedExtension(
                [
                    new UserProfileFormType(),
                ],
                []
            ),
            new ValidatorExtension($validator),
            new DoctrineOrmExtension($managerRegistry),
        ];
    }

    #[Test]
    public function testSubmitValidData(): void
    {
        $formData = [
            'email' => 'test+update@local.lan',
            'preferedLocale' => Locale::FR->value,
            'preferedTheme' => Theme::AUTO->value,
            'firstName' => 'Pierre',
            'lastName' => 'DUPOND',
            'preferenceNotification' => true,
            'preferenceNotificationIssueCreated' => true,
            'preferenceNotificationIssueUpdated' => true,
            'preferenceNotificationCommentUpdated' => false,
            'preferenceNotificationCommentCreated' => true,
        ];

        $user = UserFactory::createOne([
            'email' => 'test@local.lan',
            'firstName' => 'Alain',
            'lastName' => 'DUPONT',
            'preferredLocale' => Locale::FR,
            'preferredTheme' => Theme::AUTO,
            'preferenceNotification' => true,
            'preferenceNotificationIssueCreated' => true,
            'preferenceNotificationIssueUpdated' => true,
            'preferenceNotificationCommentUpdated' => true,
            'preferenceNotificationCommentCreated' => true,
        ]);
        $updateUser = (clone $user);
        $updateUser->setLastName('DUPOND');
        $updateUser->setFirstName('Pierre');
        $updateUser->email = 'test+update@local.lan';
        $updateUser->preferenceNotificationCommentUpdated = false;

        $model = new EditUser(
            user: $user,
        );

        $form = $this->factory->create(
            type: UserProfileFormType::class,
            data: $model,
        );

        $expected = new EditUser(
            user: $updateUser,
        );

        $form->submit($formData);
        $this->assertTrue($form->isSynchronized());
        $this->assertEquals($expected, $model);
    }
}
