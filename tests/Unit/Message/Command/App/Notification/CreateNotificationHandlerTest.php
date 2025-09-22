<?php

namespace App\Tests\Unit\Message\Command\App\Notification;

use App\Enum\Notification\NotificationType;
use App\Factory\UserFactory;
use App\Message\Command\App\Notification\CreateNotification;
use App\Message\Command\App\Notification\Handler\CreateNotificationHandler;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Zenstruck\Foundry\Test\Factories;

class CreateNotificationHandlerTest extends TestCase
{
    use Factories;

    private EntityManagerInterface|MockObject $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
    }

    public static function createNotificationDataProvider(): \Generator
    {
        yield 'can send notification' => [
            true,
        ];

        yield 'can\'t send notification' => [
            false,
        ];
    }

    #[Test]
    #[DataProvider('createNotificationDataProvider')]
    public function testDoCreateNotification(bool $userEnabled): void
    {
        $user = UserFactory::createOne([
            'enabled' => $userEnabled,
        ])->_set('id', 1);

        $this->entityManager
            ->expects($userEnabled ? self::once() : self::never())
            ->method('persist')
        ;

        $this->entityManager
            ->expects($userEnabled ? self::once() : self::never())
            ->method('flush')
        ;


        $handler = $this->generate();
        $handler(
            new CreateNotification(
                notificationType: NotificationType::ISSUE_UPDATED,
                subject: 'subject',
                body: 'body',
                link: 'link',
                user: $user,
            ),
        );
    }

    private function generate(): CreateNotificationHandler
    {
        return new CreateNotificationHandler(
            entityManager: $this->entityManager,
        );
    }
}
