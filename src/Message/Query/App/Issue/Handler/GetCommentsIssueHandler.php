<?php

namespace App\Message\Query\App\Issue\Handler;

use App\Message\Query\App\Issue\GetCommentsIssue;
use App\Repository\Jira\IssueRepository;
use JiraCloud\Issue\Comments;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class GetCommentsIssueHandler
{
    public function __construct(
        private IssueRepository $issueRepository,
    ) {
    }

    public function __invoke(GetCommentsIssue $query): Comments
    {
        return $this->issueRepository->getCommentForIssue($query->issueId);
    }
}
