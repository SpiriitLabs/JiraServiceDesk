<?php

namespace App\Tests\Unit\Service;

use App\Repository\Jira\UserRepository;
use App\Service\ReplaceAccountIdByDisplayName;
use JiraCloud\User\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReplaceAccountIdByDisplayNameTest extends TestCase
{

    private UserRepository|MockObject $userRepository;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepository::class);
    }

    public function testReplaceInCommentBody()
    {
        $user1 = $this->createMock(User::class);
        $user1->displayName = 'John Doe';
        $user2 = $this->createMock(User::class);
        $user2->displayName = 'Support Spiriit';
        $this->userRepository
            ->expects($this->any())
            ->method('getUserById')
            ->willReturnMap([
                [
                    '12',
                    $user1,
                ],
                [
                    '13',
                    $user2
                ]
            ]);

        $comment = '[~accountid:12] et [~accountid:13]';
        $service = $this->generate();

        $this->assertSame('John Doe et Support Spiriit', $service->replaceInCommentBody($comment));
    }

    private function generate(): ReplaceAccountIdByDisplayName
    {
        return new ReplaceAccountIdByDisplayName(
            $this->userRepository,
        );
    }
}