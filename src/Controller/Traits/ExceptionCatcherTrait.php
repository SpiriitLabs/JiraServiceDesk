<?php

declare(strict_types=1);

namespace App\Controller\Traits;

use Symfony\Component\Messenger\Exception\DelayedMessageHandlingException;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

trait ExceptionCatcherTrait
{
    /**
     * @psalm-param class-string|class-string[] $expectedException
     */
    protected function getHandledException(\Throwable $exception, mixed $expectedException): ?\Throwable
    {
        if (! \is_array($expectedException)) {
            $expectedException = [$expectedException];
        }

        while ($exception instanceof HandlerFailedException || $exception instanceof DelayedMessageHandlingException) {
            $exception = $exception->getPrevious();
        }

        if ($exception !== null && \in_array($exception::class, $expectedException, true)) {
            return $exception;
        }

        return null;
    }
}
