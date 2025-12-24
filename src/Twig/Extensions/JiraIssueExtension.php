<?php

declare(strict_types=1);

namespace App\Twig\Extensions;

use App\Service\IssueHtmlProcessor;
use Twig\Attribute\AsTwigFilter;

class JiraIssueExtension
{
    public function __construct(
        private readonly IssueHtmlProcessor $htmlProcessor,
    ) {
    }

    #[AsTwigFilter('preview_description')]
    public function previewAttachmentFormat($renderedDescription): string
    {
        $result = $this->htmlProcessor->updateImageSources($renderedDescription);
        $result = $this->htmlProcessor->updateJiraLinks($result);

        return $result;
    }

    #[AsTwigFilter('parse_comment_author')]
    public function parseCommentAuthor($comment): ?string
    {
        $body = html_entity_decode($comment->renderedBody);

        $parts = explode('—', $body);
        if (count($parts) > 1) {
            $author = trim(array_pop($parts));
            $author = strip_tags(str_ireplace(['<br>', '<br/>', '<br />'], '', $author));

            return $author;
        }

        $parts = preg_split('/-{4,}/', $body);
        if (count($parts) > 1) {
            $author = trim(array_pop($parts));
            $author = strip_tags(str_ireplace(['<br>', '<br/>', '<br />'], '', $author));

            return $author;
        }

        return null;
    }

    #[AsTwigFilter('issue_time_estimate_in_hour')]
    public function timeEstimateInHour($timeEstimate): ?string
    {
        return sprintf(
            '%s h',
            $timeEstimate / 3600,
        );
    }
}
