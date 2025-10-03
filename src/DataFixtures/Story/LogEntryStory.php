<?php

declare(strict_types=1);

namespace App\DataFixtures\Story;

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
