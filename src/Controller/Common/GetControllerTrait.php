<?php

declare(strict_types=1);

namespace App\Controller\Common;

use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Service\Attribute\Required;

trait GetControllerTrait
{
    use HandleTrait;

    #[Required]
    public function setMessageBus(MessageBusInterface $queryBus): void
    {
        $this->messageBus = $queryBus;
    }
}
