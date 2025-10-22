<?php

declare(strict_types=1);

namespace App\Message\Command\Admin\User\Handler;

use App\Enum\User\Role;
use App\Message\Command\Admin\User\ExportUsers;
use App\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Serializer\Encoder\EncoderInterface;

#[AsMessageHandler]
readonly class ExportUsersHandler
{
    public function __construct(
        private EncoderInterface $csvEncoder,
        private UserRepository $userRepository,
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
                'entreprise' => $user->company,
                'Compte actif' => $user->enabled,
                'dernière connexion' => $user->getLastLoginAt()?->setTimezone(new \DateTimeZone('CEST'))
                    ->format('d/m/Y H:i'),
                'projets' => $user->getProjectKeys(),
                'is_admin' => in_array(Role::ROLE_ADMIN, $user->getRoles()),
            ];
        }

        return $this->csvEncoder->encode($content, 'csv');
    }
}
