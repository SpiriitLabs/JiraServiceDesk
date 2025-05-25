<?php

namespace App\Repository\Jira;

use JiraCloud\Issue\Attachment;
use JiraCloud\Issue\Comment;
use JiraCloud\Issue\Comments;
use JiraCloud\Issue\Issue;
use JiraCloud\Issue\IssueField;
use JiraCloud\Issue\IssueService;
use JiraCloud\Issue\Transition;
use JiraCloud\JiraException;

class IssueRepository
{
    private IssueService $service;

    public function __construct()
    {
        $this->service = new IssueService();
    }

    public function getFull(string $issueId): Issue
    {
        return $this->service->get(
            issueIdOrKey: $issueId,
            paramArray: [
                'expand' => 'renderedFields,transitions',
            ]
        );
    }

    public function getCommentForIssue(string $issueId): Comments
    {
        try {
            return $this->service->getComments(
                issueIdOrKey: $issueId,
                paramArray: [
                    'startAt' => 0,
                    'expand' => 'renderedBody',
                ],
            );
        } catch (JiraException $e) {
            return new Comments();
        }
    }

    public function createComment(string $id, Comment $comment): Comment
    {
        return $this->service->addComment($id, $comment);
    }

    /**
     * @return Attachment[]
     */
    public function createAttachment(string $id, string $filePath): array
    {
        return $this->service->addAttachments($id, $filePath);
    }

    public function transitionTo(string $id, string $transitionId): void
    {
        $transition = new Transition();
        $transition->setTransitionId($transitionId);

        $this->service->transition(
            issueIdOrKey: $id,
            transition: $transition,
        );
    }

    public function create(IssueField $issueField): Issue
    {
        return $this->service->create($issueField);
    }

    public function update(Issue $issue, IssueField $issueField): ?string
    {
        return $this->service->update($issue->key, $issueField);
    }
}
