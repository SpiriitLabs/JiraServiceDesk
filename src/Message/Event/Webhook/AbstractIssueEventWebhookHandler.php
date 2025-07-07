<?php

namespace App\Message\Event\Webhook;

use App\Repository\Jira\IssueRepository;
use JiraCloud\Issue\Issue;
use JiraCloud\JiraException;
use Symfony\Contracts\Service\Attribute\Required;

class AbstractIssueEventWebhookHandler
{
    private IssueRepository $issueRepository;

    protected Issue $issue;

    #[Required]
    public function setIssueRepository(IssueRepository $issueRepository): self
    {
        $this->issueRepository = $issueRepository;

        return $this;
    }

    protected function handleIssueById(string|int $jiraIssueId): void
    {
        $this->issue = $this->issueRepository->getFull($jiraIssueId);

        if (in_array('from-client', $this->issue->fields->labels) == false) {
            throw new JiraException(sprintf("Issue #%d has not 'from-client' label", $jiraIssueId));
        }
    }
}
