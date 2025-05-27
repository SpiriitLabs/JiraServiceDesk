<?php

namespace App\Twig\Extensions;

use App\Service\IssueHtmlProcessor;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class JiraIssueExtension extends AbstractExtension
{
    public function __construct(
        private readonly IssueHtmlProcessor $htmlProcessor,
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('preview_attachment', $this->previewAttachmentFormat(...)),
        ];
    }

    public function previewAttachmentFormat($renderedDescription): string
    {
        return $this->htmlProcessor->updateImageSources($renderedDescription);
    }
}
