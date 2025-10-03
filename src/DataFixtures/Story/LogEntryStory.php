<?php

declare(strict_types=1);

namespace App\DataFixtures\Story;

use App\DataFixtures\Factory\Resident\ResidentFactory;
use App\DataFixtures\Factory\User\ResidentUserFactory;
use App\Factory\LogEntryFactory;
use Zenstruck\Foundry\Story;

class LogEntryStory extends Story
{
    #[\Override]
    public function build(): void
    {
        LogEntryFactory::createMany(50);
    }
}
