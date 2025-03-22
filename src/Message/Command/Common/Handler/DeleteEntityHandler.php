<?php

namespace App\Message\Command\Common\Handler;

use App\Message\Command\Common\DeleteEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class DeleteEntityHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(DeleteEntity $command): void
    {
        $entity = $this->entityManager->find($command->class, $command->id);

        $this->entityManager->remove($entity);
    }
}
