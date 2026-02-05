<?php

namespace App\Tests\Unit\Message\Command\Admin\Project;

use App\Factory\ProjectFactory;
use App\Message\Command\Admin\Project\ExportProjects;
use App\Message\Command\Admin\Project\Handler\ExportProjectsHandler;
use App\Repository\ProjectRepository;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Zenstruck\Foundry\Test\Factories;

class ExportProjectsHandlerTest extends TestCase
{
    use Factories;

    private CsvEncoder $csvEncoder;

    private ProjectRepository|Stub $projectRepository;

    protected function setUp(): void
    {
        $this->csvEncoder = new CsvEncoder();
        $this->projectRepository = $this->createStub(ProjectRepository::class);
    }

    #[Test]
    public function testDoExport(): void
    {
        $project = ProjectFactory::createOne();

        $this->projectRepository
            ->method('findAll')
            ->willReturn([
                $project,
            ])
        ;

        $handler = $this->generate();
        $csv = $handler(
            new ExportProjects(),
        );

        $this->assertIsString($csv);
        $this->assertStringContainsString('description', $csv);
    }

    private function generate(): ExportProjectsHandler
    {
        return new ExportProjectsHandler(
            csvEncoder: $this->csvEncoder,
            projectRepository: $this->projectRepository,
        );
    }
}
