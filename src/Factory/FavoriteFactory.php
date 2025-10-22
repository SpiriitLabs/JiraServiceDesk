<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Favorite;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Favorite>
 */
final class FavoriteFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return Favorite::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function defaults(): array|callable
    {
        return [
            'code' => self::faker()->text(5),
            'link' => self::faker()->text(),
            'name' => self::faker()->text(255),
            'user' => UserFactory::new(),
            'project' => ProjectFactory::new(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function (Favorite $favorite): void {
                $favorite->code = sprintf(
                    '%d-favorite-%s',
                    $favorite->user->getId(),
                    mb_strtolower($favorite->code),
                );

                $favorite->user->addFavorite($favorite);
            })
        ;
    }
}
