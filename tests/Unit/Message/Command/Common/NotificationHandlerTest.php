<?php

namespace App\Tests\Unit\Message\Command\Common;

use App\Enum\Notification\NotificationType;
use App\Factory\UserFactory;
use App\Message\Command\Common\Handler\NotificationHandler;
use App\Message\Command\Common\Notification;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Zenstruck\Foundry\Test\Factories;

class NotificationHandlerTest extends TestCase
{
    use Factories;

    private EntityManagerInterface|MockObject $entityManager;

    private MailerInterface|MockObject $mailer;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->mailer = $this->createMock(MailerInterface::class);
    }

    public static function emailNotificationDataProvider(): \Generator
    {
        yield 'can send notification' => [
            true,
            true,
        ];

        yield 'can\'t send notification because user preference' => [
            false,
            true,
        ];

        yield 'can\'t send notification because user inactive' => [
            true,
            false,
        ];
    }

    #[Test]
    #[DataProvider('emailNotificationDataProvider')]
    public function testDoSendEmailNotification(bool $userHasPreferenceNotification, bool $userEnabled): void
    {
        $user = UserFactory::createOne([
            'preferenceNotification' => $userHasPreferenceNotification,
            'enabled' => $userEnabled,
        ]);
        $expectSendNotification = $userEnabled && $userHasPreferenceNotification;

        $this->mailer
            ->expects($expectSendNotification ? self::once() : self::never())
            ->method('send')
        ;

        $this->entityManager
            ->expects($expectSendNotification ? self::once() : self::never())
            ->method('persist')
        ;

        $this->entityManager
            ->expects($expectSendNotification ? self::once() : self::never())
            ->method('flush')
        ;

        $handler = $this->generate();
        $handler(
            new Notification(
                $user,
                new TemplatedEmail(),
                notificationType: NotificationType::ISSUE_UPDATED,
                subject: 'subject',
                body: 'body',
                link: 'link',
            ),
        );
    }

    private function generate(): NotificationHandler
    {
        return new NotificationHandler(
            mailer: $this->mailer,
            entityManager: $this->entityManager,
        );
    }
}
