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
    private ?string $subject = null;

    #[ORM\Column(name: 'log_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $logAt = null;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $datas = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    public ?User $user = null;

    public function __construct(
        LogType $logType,
        string $subject,
        array $datas,
    ) {
        $this->logType = $logType;
        $this->subject = $subject;
        $this->datas = $datas;
        $this->logAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function getLogAt(): ?\DateTimeImmutable
    {
        return $this->logAt;
    }

    public function getDatas(): ?array
    {
        return $this->datas;
    }
}
