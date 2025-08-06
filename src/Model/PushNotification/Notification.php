<?php

namespace App\Model\PushNotification;

class Notification
{

    /**
     * @param Action[] $actions
     */
    public function __construct(
        private string $title,
        private string $body,
        private array $actions = [],
    ) {
    }

    /** @return array<mixed,mixed> */
    public function toArray(): array
    {
        $actions = [];
        foreach ($this->actions as $action) {
            $actions[] = $action->toArray();
        }

        return [
            'title' => $this->title,
            'options' => [
                'body' => $this->body,
                'actions' => $actions,
            ],
        ];
    }

}
