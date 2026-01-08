<?php

declare(strict_types=1);

namespace App\Webhook;

use App\Repository\IssueLabelRepository;

readonly class WebhookLabelFilter
{
    public function __construct(
        private IssueLabelRepository $issueLabelRepository,
    ) {
    }

    /**
     * @param array<string> $issueLabels
     */
    public function hasMatchingLabel(array $issueLabels): bool
    {
        $configuredLabels = $this->issueLabelRepository->getAllJiraLabels();

        if (empty($configuredLabels)) {
            return false;
        }

        return count(array_intersect($issueLabels, $configuredLabels)) > 0;
    }
}
