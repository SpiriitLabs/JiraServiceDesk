<?php

namespace App\Repository\Jira;

use App\Entity\Project;
use App\Message\Command\App\Issue\CreateComment;
use JiraCloud\Board\Board;
use JiraCloud\Board\BoardService;
use JiraCloud\Issue\Comment;
use JiraCloud\Issue\Issue;
use JiraCloud\Issue\IssueService;
use JiraCloud\JiraException;

class IssueRepository
{

    private IssueService $service;

    public function __construct() {
        $this->service = new IssueService();
    }

    public function getFull(string $issueId): ?Issue
    {
        try {
            return $this->service->get(
                issueIdOrKey: $issueId,
                paramArray: [
                    'expand' => 'renderedFields,transitions'
                ]
            );
        } catch (JiraException $e) {
            return null;
        }
    }

    public function getCommentForIssue(string $issueId): \JiraCloud\Issue\Comments
    {
        try {
            return $this->service->getComments(
                issueIdOrKey: $issueId,
                paramArray: [
                    'startAt' => 0,
                    'expand' => 'renderedBody'
                ],
            );
        } catch (JiraException $e) {
            return [];
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

    public function createAttachment(string $id, string $filePath): array
    {
        try {
            return $this->service->addAttachments($id, $filePath);
        } catch (JiraException $e) {
            return [];
        }
    }
}
