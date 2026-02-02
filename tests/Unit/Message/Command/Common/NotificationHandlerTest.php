<?php

namespace App\Tests\Unit\Message\Command\Common;

use App\Enum\Notification\NotificationChannel;
use App\Enum\Notification\NotificationType;
use App\Factory\UserFactory;
use App\Message\Command\Common\Handler\NotificationHandler;
use App\Message\Command\Common\Notification;
use App\Service\SlackBlockKitBuilder;
use App\Service\SlackNotificationService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Zenstruck\Foundry\Test\Factories;

class NotificationHandlerTest extends TestCase
{
    use Factories;

    private EntityManagerInterface|MockObject $entityManager;

    private MailerInterface|MockObject $mailer;

    protected EventDispatcherInterface|MockObject $eventDispatcher;

    private SlackNotificationService|MockObject $slackNotificationService;

    private SlackBlockKitBuilder|MockObject $slackBlockKitBuilder;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->mailer = $this->createMock(MailerInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->slackNotificationService = $this->createMock(SlackNotificationService::class);
        $this->slackBlockKitBuilder = $this->createMock(SlackBlockKitBuilder::class);
    }

    public static function emailNotificationDataProvider(): \Generator
    {
        yield 'can send notification with in_app and email channels' => [
            [NotificationChannel::IN_APP, NotificationChannel::EMAIL],
            true,
            true,
            true,
        ];

        yield 'can\'t send notification because user inactive' => [
            [NotificationChannel::IN_APP, NotificationChannel::EMAIL],
            false,
            false,
            false,
        ];

        yield 'no channels selected - nothing sent' => [
            [],
            true,
            false,
            false,
        ];

        yield 'in_app only - no email sent' => [
            [NotificationChannel::IN_APP],
            true,
            false,
            true,
        ];

        yield 'email only - no in_app persisted' => [
            [NotificationChannel::EMAIL],
            true,
            true,
            false,
        ];
    }

    #[Test]
    #[DataProvider('emailNotificationDataProvider')]
    public function testDoSendEmailNotification(
        array $channels,
        bool $userEnabled,
        bool $expectEmail,
        bool $expectPersist,
    ): void {
        $user = UserFactory::createOne([
            'preferenceNotificationIssueCreated' => $channels,
            'enabled' => $userEnabled,
        ]);

        $this->mailer
            ->expects($expectEmail ? self::once() : self::never())
            ->method('send')
        ;

        $this->entityManager
            ->expects($expectPersist ? self::once() : self::never())
            ->method('persist')
        ;

        $this->entityManager
            ->expects($expectPersist ? self::once() : self::never())
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
                channels: $channels,
            ),
        );
    }

    #[Test]
    public function testSlackChannelDispatchesSlackNotification(): void
    {
        $user = UserFactory::createOne([
            'enabled' => true,
            'slackBotToken' => 'xoxb-test',
            'slackMemberId' => 'U12345',
        ]);

        $this->slackBlockKitBuilder
            ->expects(self::once())
            ->method('build')
            ->willReturn([['type' => 'header']])
        ;

        $this->slackNotificationService
            ->expects(self::once())
            ->method('sendDirectMessage')
        ;

        $this->mailer
            ->expects(self::never())
            ->method('send')
        ;

        $this->entityManager
            ->expects(self::never())
            ->method('persist')
        ;

        $handler = $this->generate();
        $handler(
            new Notification(
                $user,
                null,
                notificationType: NotificationType::ISSUE_CREATED,
                subject: 'subject',
                body: 'body',
                link: 'link',
                channels: [NotificationChannel::SLACK],
            ),
        );
    }

    #[Test]
    public function testAllChannelsDispatched(): void
    {
        $user = UserFactory::createOne([
            'enabled' => true,
            'slackBotToken' => 'xoxb-test',
            'slackMemberId' => 'U12345',
        ]);

        $this->slackBlockKitBuilder
            ->expects(self::once())
            ->method('build')
            ->willReturn([])
        ;

        $this->slackNotificationService
            ->expects(self::once())
            ->method('sendDirectMessage')
        ;

        $this->mailer
            ->expects(self::once())
            ->method('send')
        ;

        $this->entityManager
            ->expects(self::once())
            ->method('persist')
        ;

        $handler = $this->generate();
        $handler(
            new Notification(
                $user,
                new TemplatedEmail(),
                notificationType: NotificationType::ISSUE_CREATED,
                subject: 'subject',
                body: 'body',
                link: 'link',
                channels: [NotificationChannel::IN_APP, NotificationChannel::EMAIL, NotificationChannel::SLACK],
            ),
        );
    }

    private function generate(): NotificationHandler
    {
        return new NotificationHandler(
            mailer: $this->mailer,
            entityManager: $this->entityManager,
            dispatcher: $this->eventDispatcher,
            slackNotificationService: $this->slackNotificationService,
            slackBlockKitBuilder: $this->slackBlockKitBuilder,
        );
    }
}
