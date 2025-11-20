<?php

declare(strict_types=1);

namespace App\Message\Query\App\Issue\Handler;

use App\Formatter\Jira\IssueAttachmentFormatter;
use App\Message\Query\App\Issue\GetFullIssue;
use App\Repository\Jira\IssueRepository;
use JiraCloud\Issue\Issue;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class GetFullIssueHandler
{
    protected const int CACHE_DURATION = 1200;

    public function __construct(
        private IssueRepository $issueRepository,
        private IssueAttachmentFormatter $issueAttachmentFormatter,
    ) {
    }

    public function __invoke(GetFullIssue $query): Issue
    {
        $cache = new FilesystemAdapter();
        $cachedIssue = $cache->getItem(sprintf('jira.full_issue_%s', $query->issueId));

        if ($cachedIssue->isHit()) {
            return $cachedIssue->get();
        }

        $issue = $this->issueRepository->getFull($query->issueId);
        $issue = $this->issueAttachmentFormatter->format($issue);

        $cachedIssue->set($issue);
        $cachedIssue->expiresAfter(self::CACHE_DURATION);
        $cache->save($cachedIssue);

        return $issue;
    }
}
