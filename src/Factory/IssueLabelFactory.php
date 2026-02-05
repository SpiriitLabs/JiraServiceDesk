<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\IssueLabel;
use App\Entity\User;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<User>
 */
final class IssueLabelFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return IssueLabel::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            'jiraLabel' => self::faker()->numberBetween(0, 10),
            'name' => self::faker()->text(255),
            'users' => [],
        ];
    }

    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function (IssueLabel $issueLabel): void {
                foreach ($issueLabel->getUsers() as $user) {
                    $user->addIssueLabel($issueLabel);
                }
            })
        ;
    }
}
