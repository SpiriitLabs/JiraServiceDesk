<?php

declare(strict_types=1);

namespace App\Subscriber\Event;

use App\Entity\User;
use App\Enum\LogEntry\Type;
use Symfony\Contracts\EventDispatcher\Event;

class NotificationEvent extends Event
{
    public const EVENT_NAME = 'notification.event';

    private bool $logHandled = false;

    public function __construct(
        public ?User $user,
        public string $message,
        public Type $type = Type::EMAIL,
        public array $extraData = [],
    ) {
    }

    public function isLogHandled(): bool
    {
        return $this->logHandled;
    }

    public function markAsHandled(): void
    {
        $this->logHandled = true;
    }
}
