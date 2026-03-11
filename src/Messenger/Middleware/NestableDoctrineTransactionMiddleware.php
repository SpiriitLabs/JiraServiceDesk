<?php

declare(strict_types=1);

namespace App\Messenger\Middleware;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Messenger\AbstractDoctrineMiddleware;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

class NestableDoctrineTransactionMiddleware extends AbstractDoctrineMiddleware
{
    protected function handleForManager(
        EntityManagerInterface $entityManager,
        Envelope $envelope,
        StackInterface $stack
    ): Envelope {
        $connection = $entityManager->getConnection();

        if ($connection->getTransactionNestingLevel() > 0) {
            return $stack->next()
                ->handle($envelope, $stack)
            ;
        }

        $connection->beginTransaction();

        $success = false;
        try {
            $envelope = $stack->next()
                ->handle($envelope, $stack)
            ;
            $entityManager->flush();
            $connection->commit();

            $success = true;

            return $envelope;
        } catch (\Throwable $exception) {
            if ($exception instanceof HandlerFailedException) {
                throw new HandlerFailedException($exception->getEnvelope()->withoutAll(
                    HandledStamp::class
                ), $exception->getWrappedExceptions());
            }

            throw $exception;
        } finally {
            if (! $success && $connection->isTransactionActive()) {
                $connection->rollBack();
            }
        }
    }
}
