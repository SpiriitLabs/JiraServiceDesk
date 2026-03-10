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
        $issueId = $query->issueId;

        if (str_contains($issueId, '-')) {
            $mappingItem = $cache->getItem(sprintf('jira.issue_key_to_id_%s', str_replace('-', '_', $issueId)));

            if ($mappingItem->isHit()) {
                $issueId = $mappingItem->get();
            }
        }

        if (! str_contains($issueId, '-')) {
            $cachedIssue = $cache->getItem(sprintf('jira.full_issue_%s', $issueId));
            if ($cachedIssue->isHit()) {
                return $cachedIssue->get();
            }
        }

        $issue = $this->issueRepository->getFull($query->issueId);
        $issue = $this->issueAttachmentFormatter->format($issue);

        $cachedIssue = $cache->getItem(sprintf('jira.full_issue_%s', $issue->id));
        $cachedIssue->set($issue);
        $cachedIssue->expiresAfter(self::CACHE_DURATION);
        $cache->save($cachedIssue);

        $mappingItem = $cache->getItem(sprintf('jira.issue_key_to_id_%s', str_replace('-', '_', $issue->key)));
        $mappingItem->set($issue->id);
        $mappingItem->expiresAfter(self::CACHE_DURATION);
        $cache->save($mappingItem);

        return $issue;
    }
}
