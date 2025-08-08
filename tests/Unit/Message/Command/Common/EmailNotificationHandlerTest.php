<?php

namespace App\Tests\Unit\Message\Command\Common;

use App\Factory\UserFactory;
use App\Message\Command\Common\EmailNotification;
use App\Message\Command\Common\Handler\EmailNotificationHandler;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Zenstruck\Foundry\Test\Factories;

class EmailNotificationHandlerTest extends TestCase
{
    use Factories;

    private MailerInterface|MockObject $mailer;

    protected function setUp(): void
    {
        $this->mailer = $this->createMock(MailerInterface::class);
    }

    public static function emailNotificationDataProvider(): \Generator
    {
        yield 'can send notification' => [
            true,
        ];

        yield 'can\'t send notification' => [
            false,
        ];
    }

    #[Test]
    #[DataProvider('emailNotificationDataProvider')]
    public function testDoSendEmailNotification(bool $userHasPreferenceNotification): void
    {
        $user = UserFactory::createOne([
            'preferenceNotification' => $userHasPreferenceNotification,
        ]);

        $this->mailer
            ->expects($userHasPreferenceNotification ? self::once() : self::never())
            ->method('send')
        ;

        $handler = $this->generate();
        $handler(
            new EmailNotification(
                $user,
                new TemplatedEmail(),
            ),
        );
    }

    private function generate(): EmailNotificationHandler
    {
        return new EmailNotificationHandler(
            mailer: $this->mailer,
        );
    }
}
