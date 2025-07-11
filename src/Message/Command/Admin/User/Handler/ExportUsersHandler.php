<?php

namespace App\Message\Command\Admin\User\Handler;

use App\Message\Command\Admin\User\ExportUsers;
use App\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Serializer\Encoder\EncoderInterface;

#[AsMessageHandler]
class ExportUsersHandler
{
    public function __construct(
        private readonly EncoderInterface $csvEncoder,
        private readonly UserRepository $userRepository,
    ) {
    }

    public function __invoke(ExportUsers $command): string
    {
        $users = $this->userRepository->findAll();

        $content = [];

        foreach ($users as $user) {
            $content[] = [
                'email' => $user->email,
                'nom' => $user->getLastName(),
                'prénom' => $user->getFirstName(),
                'société' => $user->company,
                'Compte actif' => $user->enabled,
                'projets' => $user->getProjectKeys(),
                'dernière connexion' => $user->getLastLoginAt()?->setTimezone(new \DateTimeZone('CEST'))
                    ->format('d/m/Y H:i'),
            ];
        }

        return $this->csvEncoder->encode($content, 'csv');
    }
}
