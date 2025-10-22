<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\IssueTypeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IssueTypeRepository::class)]
class IssueType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    public ?int $jiraId = null;

    #[ORM\Column(length: 255)]
    public ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    public ?string $description = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    public ?string $iconUrl = null;

    #[ORM\ManyToOne(inversedBy: 'issuesTypes')]
    #[ORM\JoinColumn(nullable: false)]
    public ?Project $project = null;

    public function __construct(?int $jiraId, ?string $name, ?string $description, ?string $iconUrl)
    {
        $this->jiraId = $jiraId;
        $this->name = $name;
        $this->description = $description;
        $this->iconUrl = $iconUrl;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return sprintf('%s (%s)', $this->name, $this->description);
    }
}
