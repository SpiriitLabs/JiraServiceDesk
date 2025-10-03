<?php

namespace App\Entity;

use App\Enum\LogEntry\Level;
use App\Enum\LogEntry\Type;
use App\Repository\LogEntryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LogEntryRepository::class)]
class LogEntry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: false, enumType: Type::class)]
    private ?Type $type = null;

    #[ORM\Column(nullable: false, enumType: Level::class)]
    private ?Level $level = null;

    #[ORM\Column(length: 255)]
    private ?string $subject;

    #[ORM\Column(name: 'log_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $logAt;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $datas;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: true)]
    private ?User $user = null;

    public function __construct(
        Type $type,
        Level $level,
        string $subject,
        array $datas,
        ?User $user = null,
    ) {
        $this->type = $type;
        $this->level = $level;
        $this->user = $user;
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

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function getLevel(): ?Level
    {
        return $this->level;
    }

    public function getLogAt(): ?\DateTimeImmutable
    {
        return $this->logAt;
    }

    public function getDatas(): ?array
    {
        return $this->datas;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
}
