<?php

declare(strict_types=1);

namespace App\Cli;

use App\Controller\App\RouteCollection;
use App\Message\Command\Admin\User\ExportUsers;
use App\Message\Command\App\Notification\SendPushNotification;
use App\Model\PushNotification\Action;
use App\Model\PushNotification\Actions;
use App\Model\PushNotification\Notification;
use App\Repository\UserRepository;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(name: 'app:user:push')]
class PushNotificationUserCommand extends Command
{
    use HandleTrait;

    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly TranslatorInterface $translator,
        private readonly UserRepository $userRepository,
        #[Autowire(env: 'VAPID_PUBLIC_KEY')]
        private string          $vapidPublicKey,
        #[Autowire(env: 'VAPID_PRIVATE_KEY')]
        private string          $vapidPrivateKey,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'email user to send')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $user = $this->userRepository->findOneBy(['email' => $input->getArgument('email')]);

        $notification = new Notification(
            title: 'Jira Service Desk',
            body: 'Test notification',
            actions: [
                new Action(
                    Actions::OPEN,
                    'Ouvrir',
                ),
                new Action(
                    Actions::CLOSE,
                    'Fermer',
                ),
            ],
        );

        $subscription = Subscription::create([
            'endpoint' => $user->pushNotificationInfo->endpoint,
            'publicKey' => $user->pushNotificationInfo->p256dh,
            'authToken' => $user->pushNotificationInfo->auth,
        ]);

        $webPush = new WebPush([
            'VAPID' => [
                'subject' => 'https://tickets.spiriit.com/',
                'publicKey' => "BJpRvdcEotkKd2Z5NQtKhYW_Sz9AfTMOB_MLvuWGHCOMpQ6gu4sYS5YY5IgQjYmsWRAhXQkxfMQsYeYWOUOZtao",
                'privateKey' => "UJMzvOEWEez0Qk_J1QvS6zliDXYuDABb84UZM4o1bq8",
            ],
        ]);


        $payload = json_encode($notification->toArray());
        try {
            $result = $webPush->sendOneNotification($subscription, $payload);
            dd($result);
        } catch (\Throwable $exception) {
            dd($exception->getMessage());
        }


        return self::SUCCESS;
    }

    #[Required]
    public function setMessageBus(MessageBusInterface $queryBus): void
    {
        $this->messageBus = $queryBus;
    }
}
