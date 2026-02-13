<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SlackNotificationService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        #[Autowire(param: 'project_name')]
        private readonly string $projectName,
    ) {
    }

    public function testConnection(User $user): true|string {
        if ($user->slackBotToken === null || $user->slackMemberId === null) {
            return 'Missing Slack credentials';
        }

        try {
            $response = $this->httpClient->request('POST', 'https://slack.com/api/chat.postMessage', [
                'headers' => [
                    'Authorization' => sprintf('Bearer %s', $user->slackBotToken),
                    'Content-Type' => 'application/json; charset=utf-8',
                ],
                'json' => [
                    'channel' => $user->slackMemberId,
                    'text' => \sprintf('Test message from %s - Your Slack integration is working !', $this->projectName),
                ],
            ]);

            $data = $response->toArray(false);

            if (($data['ok'] ?? false) === false) {
                return $data['error'] ?? 'Unknown Slack API error';
            }

            return true;
        } catch (\Throwable $e) {
            return $e->getMessage();
        }
    }

    /**
     * @param array<mixed> $blocks
     */
    public function sendDirectMessage(User $user, string $text, array $blocks = []): void
    {
        if ($user->slackBotToken === null || $user->slackMemberId === null) {
            $this->logger?->error('User slack notification data not setted failed', [
                'user' => $user->email,
            ]);

            return;
        }

        try {
            $payload = [
                'channel' => $user->slackMemberId,
                'text' => $text,
            ];

            if ($blocks !== []) {
                $payload['blocks'] = $blocks;
            }

            $response = $this->httpClient->request('POST', 'https://slack.com/api/chat.postMessage', [
                'headers' => [
                    'Authorization' => sprintf('Bearer %s', $user->slackBotToken),
                    'Content-Type' => 'application/json; charset=utf-8',
                ],
                'json' => $payload,
            ]);

            $data = $response->toArray(false);

            if (($data['ok'] ?? false) === false) {
                $this->logger?->error('Slack API error', [
                    'error' => $data['error'] ?? 'unknown',
                    'user' => $user->email,
                ]);
            }
        } catch (\Throwable $e) {
            $this->logger?->error('Slack notification failed', [
                'error' => $e->getMessage(),
                'user' => $user->email,
            ]);
        }
    }
}
