<?php

namespace App\Entity;

use App\Repository\PriorityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PriorityRepository::class)]
class Priority
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    public ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    public ?string $description = null;

    #[ORM\Column]
    public ?int $jiraId = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    public ?string $iconUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $statusColor = null;

    public function __construct(
        ?string $name,
        ?string $description,
        ?int $jiraId,
        ?string $iconUrl,
        ?string $statusColor
    ) {
        $this->name = $name;
        $this->description = $description;
        $this->jiraId = $jiraId;
        $this->iconUrl = $iconUrl;
        $this->statusColor = $statusColor;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
