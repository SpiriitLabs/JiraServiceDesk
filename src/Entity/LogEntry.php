<?php

namespace App\Entity;

use App\Enum\LogEntry\LogType;
use App\Repository\LogEntryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LogEntryRepository::class)]
class LogEntry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: false, enumType: LogType::class)]
    public ?LogType $logType = null;

    #[ORM\Column(length: 255)]
    private ?string $recipient = null;

    #[ORM\Column(length: 255)]
    private ?string $subject = null;

    #[ORM\Column(name: 'send_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $sendAt = null;

    public function __construct(
        LogType $logType,
        ?string $recipient = '',
        ?string $subject = '',
    ) {
        $this->logType = $logType;
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
