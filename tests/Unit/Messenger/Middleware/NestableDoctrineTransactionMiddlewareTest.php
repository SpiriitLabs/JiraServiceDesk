<?php

declare(strict_types=1);

namespace App\Tests\Unit\Messenger\Middleware;

use App\Messenger\Middleware\NestableDoctrineTransactionMiddleware;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Middleware\StackMiddleware;

class NestableDoctrineTransactionMiddlewareTest extends TestCase
{
    private Connection $connection;

    private EntityManagerInterface $entityManager;

    private ManagerRegistry $managerRegistry;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);

        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->entityManager
            ->method('getConnection')
            ->willReturn($this->connection)
        ;

        $this->managerRegistry = $this->createStub(ManagerRegistry::class);
        $this->managerRegistry
            ->method('getManager')
            ->willReturn($this->entityManager)
        ;
    }

    #[Test]
    public function testItSkipsTransactionWhenAlreadyInsideTransaction(): void
    {
        $this->connection
            ->method('getTransactionNestingLevel')
            ->willReturn(1)
        ;

        $this->connection
            ->expects(self::never())
            ->method('beginTransaction')
        ;

        $this->entityManager
            ->expects(self::never())
            ->method('flush')
        ;

        $this->connection
            ->expects(self::never())
            ->method('commit')
        ;

        $envelope = new Envelope(new \stdClass());
        $middleware = new NestableDoctrineTransactionMiddleware($this->managerRegistry);

        $result = $middleware->handle($envelope, new StackMiddleware());

        self::assertSame($envelope, $result);
    }

    #[Test]
    public function testItOpensTransactionWhenNotInsideTransaction(): void
    {
        $this->connection
            ->method('getTransactionNestingLevel')
            ->willReturn(0)
        ;

        $this->connection
            ->expects(self::once())
            ->method('beginTransaction')
        ;

        $this->entityManager
            ->expects(self::once())
            ->method('flush')
        ;

        $this->connection
            ->expects(self::once())
            ->method('commit')
        ;

        $envelope = new Envelope(new \stdClass());
        $middleware = new NestableDoctrineTransactionMiddleware($this->managerRegistry);

        $result = $middleware->handle($envelope, new StackMiddleware());

        self::assertSame($envelope, $result);
    }

    #[Test]
    public function testItRollsBackOnFailureWhenNotNested(): void
    {
        $this->connection
            ->method('getTransactionNestingLevel')
            ->willReturn(0)
        ;

        $this->connection
            ->expects(self::once())
            ->method('beginTransaction')
        ;

        $this->connection
            ->expects(self::never())
            ->method('commit')
        ;

        $this->connection
            ->method('isTransactionActive')
            ->willReturn(true)
        ;

        $this->connection
            ->expects(self::once())
            ->method('rollBack')
        ;

        $this->entityManager
            ->expects(self::never())
            ->method('flush')
        ;

        $throwingMiddleware = new class implements MiddlewareInterface {
            public function handle(Envelope $envelope, StackInterface $stack): Envelope
            {
                throw new \RuntimeException('Handler failed');
            }
        };

        $envelope = new Envelope(new \stdClass());
        $middleware = new NestableDoctrineTransactionMiddleware($this->managerRegistry);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Handler failed');

        $middleware->handle($envelope, new StackMiddleware($throwingMiddleware));
    }

    #[Test]
    public function testNestedDispatchScenario(): void
    {
        $beginTransactionCount = 0;

        $this->connection
            ->method('getTransactionNestingLevel')
            ->willReturnCallback(function () use (&$beginTransactionCount): int {
                return $beginTransactionCount;
            })
        ;

        $this->connection
            ->method('beginTransaction')
            ->willReturnCallback(function () use (&$beginTransactionCount): void {
                ++$beginTransactionCount;
            })
        ;

        $this->connection
            ->expects(self::once())
            ->method('commit')
        ;

        $this->entityManager
            ->expects(self::once())
            ->method('flush')
        ;

        $envelope = new Envelope(new \stdClass());
        $middleware = new NestableDoctrineTransactionMiddleware($this->managerRegistry);

        // Simulate nested dispatch: inner middleware calls the same middleware again
        $innerMiddleware = new class($middleware, $this->managerRegistry) implements MiddlewareInterface {
            public function __construct(
                private readonly MiddlewareInterface $outerMiddleware,
                private readonly ManagerRegistry $managerRegistry,
            ) {
            }

            public function handle(Envelope $envelope, StackInterface $stack): Envelope
            {
                // Simulate a sub-dispatch going through the same middleware
                $innerEnvelope = new Envelope(new \stdClass());
                $innerMiddleware = new NestableDoctrineTransactionMiddleware($this->managerRegistry);
                $innerMiddleware->handle($innerEnvelope, new StackMiddleware());

                return $envelope;
            }
        };

        $result = $middleware->handle($envelope, new StackMiddleware($innerMiddleware));

        self::assertSame($envelope, $result);
        self::assertSame(1, $beginTransactionCount);
    }
}
