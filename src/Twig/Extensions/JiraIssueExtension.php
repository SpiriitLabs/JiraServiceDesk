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
            new TwigFilter('preview_description', $this->previewAttachmentFormat(...)),
            new TwigFilter('parse_comment_author', $this->parseCommentAuthor(...)),
            new TwigFilter('issue_time_estimate_in_hour', $this->timeEstimateInHour(...)),
        ];
    }

    public function previewAttachmentFormat($renderedDescription): string
    {
        $result = $this->htmlProcessor->updateImageSources($renderedDescription);
        $result = $this->htmlProcessor->updateJiraLinks($result);

        return $result;
    }

    public function parseCommentAuthor($comment): ?string
    {
        $parts = explode('—', html_entity_decode($comment->renderedBody));
        if (count($parts) > 1) {
            $author = trim(array_pop($parts));
            $author = strip_tags(str_ireplace(['<br>', '<br/>', '<br />'], '', $author));

            return $author;
        }

        return null;
    }

    public function timeEstimateInHour($timeEstimate): ?string
    {
        return sprintf(
            '%s h',
            $timeEstimate / 3600,
        );
    }
}
