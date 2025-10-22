<?php

declare(strict_types=1);

namespace App\Message\Event\Webhook\Comment;

use Symfony\Component\Messenger\Attribute\AsMessage;
use Symfony\Component\RemoteEvent\RemoteEvent;

#[AsMessage('webhook')]
class CommentCreated extends RemoteEvent
{
    public function __construct(
        array $payload,
        string $id = '',
        string $name = 'comment-created',
    ) {
        parent::__construct(
            name: $name,
            id: sprintf('%s-%s', $name, uniqid($id)),
            payload: $payload,
        );
    }
}
