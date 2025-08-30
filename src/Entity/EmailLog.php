<?php

namespace App\Entity;

use App\Repository\EmailLogRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmailLogRepository::class)]
class EmailLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $recipient = null;

    #[ORM\Column(length: 255)]
    private ?string $subject = null;

    #[ORM\Column(name: 'send_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $sendAt = null;

    public function __construct(
        ?string $recipient = '',
        ?string $subject = '',
    ) {
        $this->recipient = $recipient;
        $this->subject = $subject;
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

    public function getRecipient(): ?string
    {
        return $this->recipient;
    }

    public function getSendAt(): ?\DateTimeImmutable
    {
        return $this->sendAt;
    }
}
