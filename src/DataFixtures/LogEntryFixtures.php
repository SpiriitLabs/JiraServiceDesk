<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\DataFixtures\Story\LogEntryStory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LogEntryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        LogEntryStory::load();
    }
}
