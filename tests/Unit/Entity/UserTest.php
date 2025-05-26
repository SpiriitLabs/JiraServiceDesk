<?php

namespace App\Tests\Unit\Entity;

use App\Factory\FavoriteFactory;
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
        $user = UserFactory::createOne()->_set('id', 1);
        $favorite = FavoriteFactory::createOne([
            'user' => $user,
        ]);

        $favoriteCodeArray = explode('-', $favorite->code);
        $favoriteCode = end($favoriteCodeArray);

        self::assertTrue($user->hasFavoriteByCode($favoriteCode));
    }

}
