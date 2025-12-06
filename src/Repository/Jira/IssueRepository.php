<?php

declare(strict_types=1);

namespace App\Repository\Jira;

use App\Entity\User;
use App\Formatter\Jira\IssueCommentsFormatter;
use App\Model\SortParams;
use JiraCloud\Issue\Attachment;
use JiraCloud\Issue\Comment;
use JiraCloud\Issue\Comments;
use JiraCloud\Issue\Issue;
use JiraCloud\Issue\IssueField;
use JiraCloud\Issue\IssueService;
use JiraCloud\Issue\Transition;
use JiraCloud\JiraException;
use Symfony\Bundle\SecurityBundle\Security;

class IssueRepository
{
    private IssueService $service;

    public function __construct(
        protected readonly IssueCommentsFormatter $issueCommentsFormatter,
        private readonly Security $security,
    ) {
        $this->service = new IssueService();
    }

    public function getFull(string $issueId, string $label = '', bool $checkLabel = true): Issue
    {
        $issue = $this->service->get(
            issueIdOrKey: $issueId,
            paramArray: [
                'expand' => 'renderedFields,transitions,changelog',
            ]
        );

        if ($checkLabel === false) {
            return $issue;
        }

        if (empty($label)) {
            $user = $this->security->getUser();
            $label = $user instanceof User ? $user->getJiraLabel() : '';
        }

        if (in_array($label, $issue->fields->labels) == false) {
            throw new JiraException(sprintf('Issue #%d has not %s label', $issueId, $label));
        }

        return $issue;
    }

    public function getByParent(string $issueId): array
    {
        $result = $this->service->search(
            jql: 'parent = ' . $issueId,
            expand: 'renderedFields,transitions,changelog',
        );
        $issues = $result->getIssues();

        $user = $this->security->getUser();
        $label = $user instanceof User ? $user->getJiraLabel() : '';

        return array_filter($issues, function ($issue) use ($label) {
            return in_array($label, $issue->fields->labels) == true;
        });
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

            return $this->issueCommentsFormatter->format($issuesComments);
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
