<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\Notification\NotificationType;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Translation\TranslatorInterface;

class SlackBlockKitBuilder
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        #[Autowire(param: 'project_name')]
        private readonly string $projectName,
    ) {
    }

    /**
     * @param array<string, string> $extraContext
     *
     * @return array<mixed>
     */
    public function build(
        NotificationType $notificationType,
        string $subject,
        string $body,
        string $link,
        string $locale = 'en',
        array $extraContext = [],
    ): array {
        $emoji = match ($notificationType) {
            NotificationType::ISSUE_CREATED => ':new:',
            NotificationType::ISSUE_UPDATED => ':pencil2:',
            NotificationType::ISSUE_DELETED => ':wastebasket:',
            NotificationType::COMMENT_CREATED => ':speech_balloon:',
            NotificationType::COMMENT_UPDATED => ':memo:',
        };

        $blocks = [];

        $blocks[] = [
            'type' => 'header',
            'text' => [
                'type' => 'plain_text',
                'text' => sprintf('%s %s', $emoji, $subject),
                'emoji' => true,
            ],
        ];

        $truncatedBody = mb_strlen($body) > 500 ? mb_substr($body, 0, 500) . '...' : $body;
        $blocks[] = [
            'type' => 'section',
            'text' => [
                'type' => 'mrkdwn',
                'text' => $truncatedBody,
            ],
        ];

        if ($extraContext !== []) {
            $elements = [];
            foreach ($extraContext as $key => $value) {
                $elements[] = [
                    'type' => 'mrkdwn',
                    'text' => sprintf('*%s:* %s', $key, $value),
                ];
            }
            $blocks[] = [
                'type' => 'context',
                'elements' => $elements,
            ];
        }

        $blocks[] = [
            'type' => 'divider',
        ];

        $buttonLabel = $this->translator->trans(
            id: 'slack.button.view',
            parameters: [
                '%project_name%' => $this->projectName,
            ],
            domain: 'app',
            locale: $locale,
        );

        $blocks[] = [
            'type' => 'actions',
            'elements' => [
                [
                    'type' => 'button',
                    'text' => [
                        'type' => 'plain_text',
                        'text' => $buttonLabel,
                        'emoji' => true,
                    ],
                    'url' => $link,
                    'style' => 'primary',
                ],
            ],
        ];

        return $blocks;
    }
}
