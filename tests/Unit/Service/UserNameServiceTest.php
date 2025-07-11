<?php

namespace App\Tests\Unit\Service;

use App\Service\UserNameService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Zenstruck\Foundry\InMemory\AsInMemoryTest;

#[AsInMemoryTest]
class UserNameServiceTest extends TestCase
{

    #[Test]
    public function testItMustBeReturnQuestionMark(): void
    {
        $this->assertSame('??', UserNameService::initials());

        $this->assertSame('??', UserNameService::initials(firstName: 'John'));

        $this->assertSame('??', UserNameService::initials(lastName: 'Doe'));
    }

    #[Test]
    public function testItMustBeReturnInitials(): void
    {
        $this->assertSame('JD', UserNameService::initials(firstName: 'John', lastName: 'Doe'));
    }
}