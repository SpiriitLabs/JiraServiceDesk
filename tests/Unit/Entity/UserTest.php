<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Favorite;
use App\Entity\Notification;
use App\Entity\Project;
use App\Entity\User;
use App\Factory\FavoriteFactory;
use App\Factory\IssueLabelFactory;
use App\Factory\NotificationFactory;
use App\Factory\ProjectFactory;
use App\Factory\UserFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Zenstruck\Foundry\InMemory\AsInMemoryTest;
use Zenstruck\Foundry\Test\Factories;

#[AsInMemoryTest]
class UserTest extends TestCase
{
    use Factories;

    #[Test]
    public function testItMustBeHaveFavoriteByCode(): void
    {
        /** @var User $user */
        $user = UserFactory::createOne()->_set('id', 1);

        /** @var Project $project */
        $project = ProjectFactory::createOne();

        /** @var Favorite $favorite */
        $favorite = FavoriteFactory::createOne([
            'user' => $user,
            'project' => $project,
        ]);

        $favoriteCodeArray = explode('-', $favorite->code);
        $favoriteCode = end($favoriteCodeArray);

        self::assertTrue($user->hasFavoriteByCode($favoriteCode));
    }

    #[Test]
    public function testItMustBeHaveLastNameInCaps(): void
    {
        $user = new User(
            email: 'email@email.tld',
            firstName: 'firstName',
            lastName: 'TestofLastName',
        );

        self::assertSame('FirstName', $user->getFirstName());
        self::assertSame('TESTOFLASTNAME', $user->getLastName());

        // User with special letters.
        $user = new User(
            email: 'email@email.tld',
            firstName: 'firstName',
            lastName: 'Désirè',
        );

        self::assertSame('FirstName', $user->getFirstName());
        self::assertSame('DÉSIRÈ', $user->getLastName());
    }

    #[Test]
    public function testItMustBeHasNotViewedNotifications(): void
    {
        /** @var User $user */
        $user = UserFactory::createOne()->_set('id', 1);

        /** @var Notification $notification */
        $notification = NotificationFactory::createOne([
            'user' => $user,
            'isViewed' => false,
        ]);

        self::assertTrue($user->hasNotViewedNotifications());
    }

    #[Test]
    public function testItMustBeGetOnlyNotViewedNotifications(): void
    {
        /** @var User $user */
        $user = UserFactory::createOne()->_set('id', 1);

        NotificationFactory::createOne([
            'user' => $user,
            'isViewed' => false,
        ]);
        NotificationFactory::createOne([
            'user' => $user,
            'isViewed' => false,
        ]);
        NotificationFactory::createOne([
            'user' => $user,
            'isViewed' => true,
        ]);

        self::assertCount(2, $user->getNotViewedNotifications());
    }

    #[Test]
    public function testItGetJiraLabel(): void
    {
        /** @var User $user */
        $user = UserFactory::createOne()->_set('id', 1);

        IssueLabelFactory::createOne([
            'users' => [$user],
            'jiraLabel' => 'label',
            'name' => 'label',
        ]);

        self::assertSame('label', $user->getJiraLabel());
    }
}
