<?php

namespace App\Message\Query\App\Issue\Handler;

use App\Formatter\Jira\IssueAttachmentFormatter;
use App\Message\Query\App\Issue\GetFullIssue;
use App\Repository\Jira\IssueRepository;
use JiraCloud\Issue\Issue;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class GetFullIssueHandler
{
    public function __construct(
        private IssueRepository $issueRepository,
        private IssueAttachmentFormatter $issueAttachmentFormatter,
    ) {
    }

    public function __invoke(GetFullIssue $query): Issue
    {
        $issue = $this->issueRepository->getFull($query->issueId);
        $issue = $this->issueAttachmentFormatter->format($issue);

        return $issue;
    }
}
