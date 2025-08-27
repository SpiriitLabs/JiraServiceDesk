<?php

namespace App\Repository\Jira;

use App\Model\SortParams;
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
        $issue = $this->service->get(
            issueIdOrKey: $issueId,
            paramArray: [
                'expand' => 'renderedFields,transitions,changelog',
            ]
        );

        if (in_array('from-client', $issue->fields->labels) == false) {
            throw new JiraException(sprintf("Issue #%d has not 'from-client' label", $issueId));
        }

        return $issue;
    }

    public function getCommentForIssue(string $issueId, SortParams $sort): Comments
    {
        try {
            $issuesComments = $this->service->getComments(
                issueIdOrKey: $issueId,
                paramArray: [
                    'startAt' => 0,
                    'expand' => 'renderedBody',
                    'orderBy' => $sort,
                ],
            );

            $issuesComments->comments = array_filter(
                $issuesComments->comments,
                function ($comment) {
                    if ($comment->visibility == null) {
                        return true;
                    }

                    return false;
                }
            );
            $issuesComments->total = count($issuesComments->comments);

            $formatedComments = [];
            foreach ($issuesComments->comments as $comment) {
                $formatedComments[$comment->id] = $comment;
                $formatedComments[$comment->id]->childs = [];
            }
            foreach (array_reverse($formatedComments) as $comment) {
                if (! empty($comment->parentId)) {
                    $formatedComments[$comment->parentId]->childs[] = $comment;
                    unset($formatedComments[$comment->id]);
                }
            }
            $issuesComments->comments = $formatedComments;

            return $issuesComments;
        } catch (JiraException $e) {
            return new Comments();
        }
    }

    public function createComment(string $id, Comment $comment): Comment
    {
        return $this->service->addComment($id, $comment);
    }

    public function getComment(string $issueId, string $commentId): Comment
    {
        return $this->service->getComment($issueId, $commentId);
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
