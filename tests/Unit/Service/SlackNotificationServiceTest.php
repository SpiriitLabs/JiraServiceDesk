<?php

namespace App\Tests\Unit\Service;

use App\Entity\User;
use App\Service\SlackNotificationService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SlackNotificationServiceTest extends TestCase
{
    #[Test]
    public function testSendDirectMessageCallsSlackApi(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn(['ok' => true]);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient
            ->expects(self::once())
            ->method('request')
            ->with(
                'POST',
                'https://slack.com/api/chat.postMessage',
                self::callback(function (array $options): bool {
                    return $options['json']['channel'] === 'U12345'
                        && $options['json']['text'] === 'Test message'
                        && str_contains($options['headers']['Authorization'], 'xoxb-test-token');
                }),
            )
            ->willReturn($response)
        ;

        $service = new SlackNotificationService($httpClient);

        $user = new User('test@example.com', 'John', 'Doe');
        $user->slackBotToken = 'xoxb-test-token';
        $user->slackMemberId = 'U12345';

        $service->sendDirectMessage($user, 'Test message', [['type' => 'header']]);
    }

    #[Test]
    public function testSendDirectMessageSkipsWhenNoToken(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient
            ->expects(self::never())
            ->method('request')
        ;

        $service = new SlackNotificationService($httpClient);

        $user = new User('test@example.com', 'John', 'Doe');
        $user->slackBotToken = null;
        $user->slackMemberId = 'U12345';

        $service->sendDirectMessage($user, 'Test message');
    }

    #[Test]
    public function testSendDirectMessageSkipsWhenNoMemberId(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient
            ->expects(self::never())
            ->method('request')
        ;

        $service = new SlackNotificationService($httpClient);

        $user = new User('test@example.com', 'John', 'Doe');
        $user->slackBotToken = 'xoxb-test-token';
        $user->slackMemberId = null;

        $service->sendDirectMessage($user, 'Test message');
    }

    #[Test]
    public function testSendDirectMessageLogsApiError(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn(['ok' => false, 'error' => 'channel_not_found']);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('request')->willReturn($response);

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(self::once())
            ->method('error')
            ->with('Slack API error', self::callback(function (array $context): bool {
                return $context['error'] === 'channel_not_found';
            }))
        ;

        $service = new SlackNotificationService($httpClient);
        $service->setLogger($logger);

        $user = new User('test@example.com', 'John', 'Doe');
        $user->slackBotToken = 'xoxb-test-token';
        $user->slackMemberId = 'U12345';

        $service->sendDirectMessage($user, 'Test message');
    }
}
