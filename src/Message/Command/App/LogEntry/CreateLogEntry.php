<?php

namespace App\Message\Command\App\LogEntry;

use App\Enum\LogEntry\LogType;

class CreateLogEntry
{
    public function __construct(
        public LogType $logType,
        public ?string $recipient = '',
        public ?string $subject = '',
    ) {
    }
}
