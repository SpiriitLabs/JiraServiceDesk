<?php

namespace App\Entity;

use App\Enum\User\Locale;
use App\Enum\User\Theme;
use App\Repository\UserRepository;
use App\Service\UserNameService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\SoftDeleteable;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\HasLifecycleCallbacks]
#[SoftDeleteable]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use TimestampableEntity;
    use SoftDeleteableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    public ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    public ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $company = null;

    #[ORM\Column(nullable: false, enumType: Locale::class)]
    public Locale $preferredLocale = Locale::FR;

    #[ORM\Column(nullable: false, enumType: Theme::class)]
    public Theme $preferredTheme = Theme::AUTO;

    /**
     * @var Collection<int, Project>
     */
    #[ORM\ManyToMany(targetEntity: Project::class, mappedBy: 'users')]
    private Collection $projects;

    /**
     * @var Collection<int, Favorite>
     */
    #[ORM\OneToMany(targetEntity: Favorite::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $favorites;

    #[ORM\Column(type: Types::BOOLEAN)]
    public bool $preferenceNotification = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    public bool $preferenceNotificationIssueCreated = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    public bool $preferenceNotificationIssueUpdated = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    public bool $preferenceNotificationCommentCreated = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    public bool $preferenceNotificationCommentUpdated = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    public bool $preferenceNotificationCommentOnlyOnTag = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    public bool $hasCompletedIntroduction = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    public bool $enabled = true;

    #[ORM\ManyToOne]
    public ?Project $defaultProject = null;

    #[ORM\Column(name: 'last_login_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $lastLoginAt = null;

    public function __construct(
        ?string $email,
        ?string $firstName,
        ?string $lastName,
        ?string $company = null,
        bool $enabled = true
    ) {
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->company = $company;
        $this->projects = new ArrayCollection();
        $this->favorites = new ArrayCollection();
        $this->enabled = $enabled;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = strtoupper($lastName);
    }

    public function getFullName(): ?string
    {
        return sprintf('%s %s', $this->firstName, $this->lastName);
    }

    public function getInitials(): ?string
    {
        return UserNameService::initials($this->firstName, $this->lastName);
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection<int, Project>
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): static
    {
        if (! $this->projects->contains($project)) {
            $this->projects->add($project);
            $project->addUser($this);
        }

        return $this;
    }

    public function removeProject(Project $project): static
    {
        if ($this->projects->removeElement($project)) {
            $project->removeUser($this);
        }

        return $this;
    }

    public function clearProjects(): static
    {
        foreach ($this->projects as $project) {
            $this->removeProject($project);
        }

        return $this;
    }

    /**
     * @return Collection<int, Favorite>
     */
    public function getFavorites(): Collection
    {
        return $this->favorites;
    }

    public function addFavorite(Favorite $favorite): static
    {
        if (! $this->favorites->contains($favorite)) {
            $this->favorites->add($favorite);
            $favorite->user = $this;
        }

        return $this;
    }

    public function removeFavorite(Favorite $favorite): static
    {
        if ($this->favorites->removeElement($favorite)) {
            // set the owning side to null (unless already changed)
            if ($favorite->user === $this) {
                $favorite->user = null;
            }
        }

        return $this;
    }

    public function hasFavoriteByCode(string $code): bool
    {
        foreach ($this->favorites as $favorite) {
            if ($favorite->code === sprintf('%d-favorite-%s', $this->id, $code)) {
                return true;
            }
        }

        return false;
    }

    public function getLastLoginAt(): ?\DateTimeImmutable
    {
        return $this->lastLoginAt;
    }

    public function setLastLoginAt(?\DateTimeImmutable $lastLoginAt): self
    {
        $this->lastLoginAt = $lastLoginAt;

        return $this;
    }
}
