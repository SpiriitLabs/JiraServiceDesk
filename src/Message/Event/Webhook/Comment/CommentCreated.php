<?php

namespace App\Message\Event\Webhook\Comment;

use Symfony\Component\RemoteEvent\RemoteEvent;

class CommentCreated extends RemoteEvent
{
    public function __construct(
        array $payload,
        string $id = '',
        string $name = 'comment-created',
    ) {
        parent::__construct($name, uniqid($id), $payload);
    }
}
