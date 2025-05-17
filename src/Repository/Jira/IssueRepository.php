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

    public function getFull(string $issueId): ?Issue
    {
        try {
            return $this->service->get(
                issueIdOrKey: $issueId,
                paramArray: [
                    'expand' => 'renderedFields,transitions',
                ]
            );
        } catch (JiraException $e) {
            return null;
        }
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

    public function createComment(string $id, Comment $comment): ?Comment
    {
        try {
            return $this->service->addComment($id, $comment);
        } catch (JiraException $e) {
            return null;
        }
    }

    /**
     * @return array|Attachment[]
     */
    public function createAttachment(string $id, string $filePath): array
    {
        try {
            return $this->service->addAttachments($id, $filePath);
        } catch (JiraException $e) {
            return [];
        }
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

    public function create(IssueField $issueField): ?Issue
    {
        try {
            return $this->service->create($issueField);
        } catch (JiraException $e) {
            return null;
        }
    }

    public function update(Issue $issue, IssueField $issueField): ?string
    {
        try {
            return $this->service->update($issue->key, $issueField);
        } catch (JiraException $e) {
            return null;
        }
    }
}
