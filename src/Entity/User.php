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
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Mapping\Annotation\Loggable;
use Gedmo\Mapping\Annotation\SoftDeleteable;
use Gedmo\Mapping\Annotation\Versioned;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\HasLifecycleCallbacks]
#[Loggable]
#[SoftDeleteable]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use TimestampableEntity;
    use SoftDeleteableEntity;
    use BlameableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Versioned]
    public ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    #[Versioned]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Versioned]
    public ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Versioned]
    private ?string $lastName = null;

    public string $fullName {
        get => sprintf('%s %s', $this->firstName, $this->lastName);
    }

    public string $initials {
        get => UserNameService::initials($this->firstName, $this->lastName);
    }

    #[ORM\Column(length: 255, nullable: true)]
    #[Versioned]
    public ?string $company = null;

    #[ORM\Column(nullable: false, enumType: Locale::class)]
    #[Versioned]
    public Locale $preferredLocale = Locale::FR;

    #[ORM\Column(nullable: false, enumType: Theme::class)]
    #[Versioned]
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
    #[Versioned]
    public bool $preferenceNotification = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    #[Versioned]
    public bool $preferenceNotificationIssueCreated = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    #[Versioned]
    public bool $preferenceNotificationIssueUpdated = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    #[Versioned]
    public bool $preferenceNotificationCommentCreated = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    #[Versioned]
    public bool $preferenceNotificationCommentUpdated = false;

    #[ORM\Column(type: Types::BOOLEAN)]
    #[Versioned]
    public bool $hasCompletedIntroduction = false;

    public function __construct(?string $email, ?string $firstName, ?string $lastName, ?string $company = null)
    {
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->company = $company;
        $this->projects = new ArrayCollection();
        $this->favorites = new ArrayCollection();
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

    public function getLoggable(): string
    {
        return sprintf(
            '[%s] %s %s (%s)',
            $this->id,
            $this->firstName,
            $this->lastName,
            $this->email,
        );
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
}
