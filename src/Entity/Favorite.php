<?php

namespace App\Entity;

use App\Repository\FavoriteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FavoriteRepository::class)]
class Favorite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    public ?string $name = null;

    #[ORM\Column(length: 255)]
    public ?string $key = null;

    #[ORM\Column(type: Types::TEXT)]
    public ?string $link = null;

    #[ORM\ManyToOne(inversedBy: 'favorites')]
    #[ORM\JoinColumn(nullable: false)]
    public ?User $user = null;

    public function __construct(?string $name, ?string $key, ?string $link, ?User $user)
    {
        $this->name = $name;
        $this->key = $key;
        $this->link = $link;
        $this->user = $user;
    }

    public function getId(): ?int
    {
        return $this->id;
    }



}
