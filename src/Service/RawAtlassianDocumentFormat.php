<?php

declare(strict_types=1);

namespace App\Service;

use JiraCloud\ADF\AtlassianDocumentFormat;

/**
 * Passes raw ADF JSON to Jira without parsing through DH\Adf Document::load().
 * This preserves the exact ADF structure from the editor, avoiding any
 * transformation that Document::load() + jsonSerialize() might apply.
 */
class RawAtlassianDocumentFormat extends AtlassianDocumentFormat
{
    /**
     * @param array<mixed> $rawData
     */
    public function __construct(
        private readonly array $rawData,
    ) {
    }

    /**
     * @return array<mixed>
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize(): array
    {
        return $this->rawData;
    }
}
