<?php

namespace App\Model\PushNotification;

class Action
{

    public function __construct(
        private Actions $action,
        private string $title,
    ) {
    }

    /** @return array<mixed,mixed> */
    public function toArray(): array
    {
        return [
            'action' => $this->action->value,
            'title' => $this->title,
        ];
    }

}
