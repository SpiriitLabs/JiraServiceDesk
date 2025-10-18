<?php

namespace App\Message\Command\App\LogEntry;

use App\Entity\User;
use App\Enum\LogEntry\Level;
use App\Enum\LogEntry\Type;

class CreateLogEntry
{
    public function __construct(
        public Type $type,
        public Level $level = Level::INFO,
        public ?string $subject = '',
        public array $datas = [],
        public ?User $user = null,
    ) {
    }
}
