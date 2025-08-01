<?php

namespace App\Message\Event\Webhook\Issue;

use Symfony\Component\Messenger\Attribute\AsMessage;
use Symfony\Component\RemoteEvent\RemoteEvent;

#[AsMessage('webhook')]
class IssueDeleted extends RemoteEvent
{
    public function __construct(
        array $payload,
        string $id = '',
        string $name = 'issue-deleted',
    ) {
        parent::__construct(
            name: $name,
            id: sprintf('%s-%s', $name, uniqid($id)),
            payload: $payload,
        );
    }
}
