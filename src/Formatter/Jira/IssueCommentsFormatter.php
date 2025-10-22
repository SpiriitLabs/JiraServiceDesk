<?php

declare(strict_types=1);

namespace App\Formatter\Jira;

use JiraCloud\Issue\Comments;

class IssueCommentsFormatter
{
    public function format(Comments $comments): Comments
    {
        $comments->comments = array_filter(
            $comments->comments,
            function ($comment) {
                if ($comment->visibility == null) {
                    return true;
                }

                return false;
            }
        );
        $comments->total = count($comments->comments);

        $formatedComments = [];
        foreach ($comments->comments as $comment) {
            $formatedComments[$comment->id] = $comment;
            $formatedComments[$comment->id]->childs = [];
        }
        foreach (array_reverse($formatedComments) as $comment) {
            if (! empty($comment->parentId)) {
                $formatedComments[$comment->parentId]->childs[] = $comment;
                unset($formatedComments[$comment->id]);
            }
        }
        $comments->comments = $formatedComments;

        return $comments;
    }
}
