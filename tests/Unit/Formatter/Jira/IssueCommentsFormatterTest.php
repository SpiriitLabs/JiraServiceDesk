<?php

namespace App\Tests\Unit\Formatter\Jira;

use App\Formatter\Jira\IssueCommentsFormatter;
use JiraCloud\Issue\Comment;
use JiraCloud\Issue\Comments;
use JiraCloud\Issue\Visibility;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IssueCommentsFormatterTest extends TestCase
{
    private IssueCommentsFormatter|MockObject $formatter;

    protected function setUp(): void
    {
        $this->formatter = new IssueCommentsFormatter();
    }

    #[Test]
    public function testFormat(): void
    {
        $comments = new Comments();
        $comments->comments = [];

        $comment = new Comment();
        $comment->id = 1;
        $comment->visibility = null;
        $comments->comments[] = $comment;

        $comment = new Comment();
        $comment->id = 2;
        $comment->visibility = new Visibility();
        $comments->comments[] = $comment;

        $comment = new Comment();
        $comment->id = 3;
        $comment->parentId = 1;
        $comment->visibility = null;
        $comments->comments[] = $comment;

        $result = $this->formatter->format($comments);

        $this->assertIsArray($result->comments);
        $this->assertCount(1, $result->comments);
    }
}
