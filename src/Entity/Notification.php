<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\Notification\NotificationType;
use App\Repository\NotificationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NotificationRepository::class)]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: false, enumType: NotificationType::class)]
    public ?NotificationType $notificationType = null;

    #[ORM\Column(length: 255)]
    private ?string $subject;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $body;

    #[ORM\Column(type: Types::TEXT)]
    public ?string $link = null;

    #[ORM\ManyToOne(inversedBy: 'notifications')]
    #[ORM\JoinColumn(nullable: false)]
    public ?User $user = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    public bool $isViewed = false;

    #[ORM\Column(name: 'send_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $sendAt;

    public function __construct(
        NotificationType $notificationType,
        string $subject,
        string $body,
        string $link,
        User $user
    ) {
        $this->notificationType = $notificationType;
        $this->subject = $subject;
        $this->body = $body;
        $this->link = $link;
        $this->user = $user;
        $this->sendAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function getSendAt(): ?\DateTimeImmutable
    {
        return $this->sendAt;
    }
}
