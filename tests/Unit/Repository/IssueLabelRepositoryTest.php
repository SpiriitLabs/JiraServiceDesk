<?php

declare(strict_types=1);

namespace App\Tests\Unit\Repository;

use App\Factory\IssueLabelFactory;
use App\Repository\IssueLabelRepository;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class IssueLabelRepositoryTest extends KernelTestCase
{
    use Factories;
    use ResetDatabase;

    private IssueLabelRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->repository = self::getContainer()->get(IssueLabelRepository::class);
    }

    #[Test]
    public function testGetAllJiraLabelsReturnsArrayOfStrings(): void
    {
        IssueLabelFactory::createOne([
            'jiraLabel' => 'from-client',
            'name' => 'From Client',
        ]);
        IssueLabelFactory::createOne([
            'jiraLabel' => 'support',
            'name' => 'Support',
        ]);

        $labels = $this->repository->getAllJiraLabels();

        self::assertCount(2, $labels);
        self::assertContains('from-client', $labels);
        self::assertContains('support', $labels);
    }

    #[Test]
    public function testGetAllJiraLabelsReturnsEmptyArrayWhenNoLabels(): void
    {
        $labels = $this->repository->getAllJiraLabels();

        self::assertIsArray($labels);
        self::assertEmpty($labels);
    }
}
