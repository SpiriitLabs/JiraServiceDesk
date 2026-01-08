<?php

declare(strict_types=1);

namespace App\Tests\Unit\Webhook;

use App\Repository\IssueLabelRepository;
use App\Webhook\WebhookLabelFilter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WebhookLabelFilterTest extends TestCase
{
    private IssueLabelRepository|MockObject $issueLabelRepository;

    protected function setUp(): void
    {
        $this->issueLabelRepository = $this->createMock(IssueLabelRepository::class);
    }

    public static function labelMatchingDataProvider(): \Generator
    {
        yield 'issue has matching label' => [
            ['from-client', 'bug'],
            ['from-client', 'support'],
            true,
        ];

        yield 'issue has no matching labels' => [
            ['bug', 'feature'],
            ['from-client', 'support'],
            false,
        ];

        yield 'issue has multiple labels, one matches' => [
            ['bug', 'feature', 'support'],
            ['from-client', 'support'],
            true,
        ];

        yield 'no labels in database' => [
            ['from-client', 'bug'],
            [],
            false,
        ];

        yield 'empty labels array on issue' => [
            [],
            ['from-client', 'support'],
            false,
        ];

        yield 'both issue and database have empty labels' => [
            [],
            [],
            false,
        ];
    }

    /**
     * @param array<string> $issueLabels
     * @param array<string> $databaseLabels
     */
    #[Test]
    #[DataProvider('labelMatchingDataProvider')]
    public function testHasMatchingLabel(array $issueLabels, array $databaseLabels, bool $expectedResult): void
    {
        $this->issueLabelRepository
            ->method('getAllJiraLabels')
            ->willReturn($databaseLabels);

        $filter = new WebhookLabelFilter($this->issueLabelRepository);

        self::assertSame($expectedResult, $filter->hasMatchingLabel($issueLabels));
    }
}
