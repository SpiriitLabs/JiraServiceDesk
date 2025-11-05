<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\IssueLabelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: IssueLabelRepository::class)]
class IssueLabel
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    public string $jiraLabel;

    #[ORM\Column(length: 255)]
    public string $name;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'issueLabel')]
    private Collection $users;

    public function __construct(
        string $jiraLabel,
        string $name,
    ) {
        $this->jiraLabel = $jiraLabel;
        $this->name = $name;
        $this->users = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (! $this->users->contains($user)) {
            $this->users->add($user);
            $user->setIssueLabel($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            if ($user->getIssueLabel() === $this) {
                $user->setIssueLabel(null);
            }
        }

        return $this;
    }
}
