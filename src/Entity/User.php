<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\Notification\NotificationChannel;
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
use Spiriit\Bundle\AuthLogBundle\Entity\AuthenticableLogInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use function Symfony\Component\String\u;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\HasLifecycleCallbacks]
#[SoftDeleteable]
class User implements UserInterface, PasswordAuthenticatedUserInterface, AuthenticableLogInterface
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
    #[ORM\Column(nullable: true)]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    public ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\Column(nullable: false, enumType: Locale::class)]
    public Locale $preferredLocale = Locale::FR;

    #[ORM\Column(nullable: false, enumType: Theme::class)]
    public Theme $preferredTheme = Theme::AUTO;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $company = null;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $slackBotToken = null;

    #[ORM\Column(length: 255, nullable: true)]
    public ?string $slackMemberId = null;

    /**
     * @var NotificationChannel[]
     */
    #[ORM\Column(type: Types::JSON)]
    public array $preferenceNotificationIssueCreated = [NotificationChannel::IN_APP, NotificationChannel::EMAIL];

    /**
     * @var NotificationChannel[]
     */
    #[ORM\Column(type: Types::JSON)]
    public array $preferenceNotificationIssueUpdated = [NotificationChannel::IN_APP, NotificationChannel::EMAIL];

    /**
     * @var NotificationChannel[]
     */
    #[ORM\Column(type: Types::JSON)]
    public array $preferenceNotificationIssueDeleted = [NotificationChannel::IN_APP, NotificationChannel::EMAIL];

    /**
     * @var NotificationChannel[]
     */
    #[ORM\Column(type: Types::JSON)]
    public array $preferenceNotificationCommentCreated = [NotificationChannel::IN_APP, NotificationChannel::EMAIL];

    /**
     * @var NotificationChannel[]
     */
    #[ORM\Column(type: Types::JSON)]
    public array $preferenceNotificationCommentUpdated = [NotificationChannel::IN_APP, NotificationChannel::EMAIL];

    /**
     * @var NotificationChannel[]
     */
    #[ORM\Column(type: Types::JSON)]
    public array $preferenceNotificationCommentOnlyOnTag = [];

    #[ORM\Column(type: Types::BOOLEAN)]
    public bool $enabled = true;

    #[ORM\ManyToOne]
    public ?Project $defaultProject = null;

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

    #[ORM\Column(name: 'last_login_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $lastLoginAt = null;

    /**
     * @var Collection<int, Notification>
     */
    #[ORM\OneToMany(targetEntity: Notification::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $notifications;

    /**
     * @var Collection<int, IssueLabel>
     */
    #[ORM\ManyToMany(targetEntity: IssueLabel::class, inversedBy: 'users')]
    #[ORM\JoinTable(name: 'user_issue_label')]
    private Collection $issueLabels;

    #[ORM\Column(name: 'password_changed_at', type: 'datetime_immutable', nullable: true)]
    private ?\DateTimeImmutable $passwordChangedAt;

    public function __construct(
        ?string $email,
        ?string $firstName,
        ?string $lastName,
        ?string $company = null,
        bool $enabled = true,
    ) {
        $this->email = $email;
        $this->company = $company;
        $this->projects = new ArrayCollection();
        $this->favorites = new ArrayCollection();
        $this->notifications = new ArrayCollection();
        $this->issueLabels = new ArrayCollection();
        $this->enabled = $enabled;

        $this->setFirstName($firstName);
        $this->setLastName($lastName);
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

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = ucfirst($firstName);
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = u($lastName)
            ->upper()
            ->toString()
        ;
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
     * @return list<string>
     *
     * @see UserInterface
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
        $this->passwordChangedAt = new \DateTimeImmutable('now');

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

    public function clearProjects(): static
    {
        foreach ($this->projects as $project) {
            $this->removeProject($project);
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

    public function getProjectKeys(): string
    {
        $projects = array_map(function (
            $project,
        ) {
            return $project->jiraKey;
        }, $this->projects
            ->toArray());

        return implode(', ', $projects);
    }

    /**
     * @return Collection<int, Notification>
     */
    public function getNotifications(): Collection
    {
        return $this->notifications;
    }

    public function addNotification(Notification $notification): static
    {
        if (! $this->notifications->contains($notification)) {
            $this->notifications->add($notification);
            $notification->user = $this;
        }

        return $this;
    }

    public function removeNotification(Notification $notification): static
    {
        if ($this->notifications->removeElement($notification)) {
            if ($notification->user === $this) {
                $notification->user = null;
            }
        }

        return $this;
    }

    public function getNotViewedNotifications(): array
    {
        return array_filter($this->notifications->toArray(), function (
            Notification $notification,
        ) {
            return $notification->isViewed == false;
        });
    }

    public function hasNotViewedNotifications(): bool
    {
        return count($this->getNotViewedNotifications()) > 0;
    }

    public function getAuthenticationLogFactoryName(): string
    {
        return 'user';
    }

    public function getAuthenticationLogsToEmail(): string
    {
        return $this->email;
    }

    public function getAuthenticationLogsToEmailName(): string
    {
        return $this->getFullName();
    }

    /**
     * @return Collection<int, IssueLabel>
     */
    public function getIssueLabels(): Collection
    {
        return $this->issueLabels;
    }

    public function addIssueLabel(IssueLabel $issueLabel): static
    {
        if (! $this->issueLabels->contains($issueLabel)) {
            $this->issueLabels->add($issueLabel);
        }

        return $this;
    }

    public function removeIssueLabel(IssueLabel $issueLabel): static
    {
        $this->issueLabels->removeElement($issueLabel);

        return $this;
    }

    public function clearIssueLabels(): static
    {
        $this->issueLabels->clear();

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getJiraLabels(): array
    {
        return $this->issueLabels->map(fn (IssueLabel $label) => $label->jiraLabel)
            ->getValues()
        ;
    }

    /**
     * @param list<string> $issueLabels
     */
    public function hasAnyJiraLabel(array $issueLabels): bool
    {
        return count(array_intersect($this->getJiraLabels(), $issueLabels)) > 0;
    }

    public function getPasswordChangedAt(): ?\DateTimeImmutable
    {
        return $this->passwordChangedAt;
    }

    #[ORM\PostLoad]
    public function hydrateNotificationChannels(): void
    {
        $this->preferenceNotificationIssueCreated = $this->mapToEnumArray($this->preferenceNotificationIssueCreated);
        $this->preferenceNotificationIssueUpdated = $this->mapToEnumArray($this->preferenceNotificationIssueUpdated);
        $this->preferenceNotificationIssueDeleted = $this->mapToEnumArray($this->preferenceNotificationIssueDeleted);
        $this->preferenceNotificationCommentCreated = $this->mapToEnumArray(
            $this->preferenceNotificationCommentCreated
        );
        $this->preferenceNotificationCommentUpdated = $this->mapToEnumArray(
            $this->preferenceNotificationCommentUpdated
        );
        $this->preferenceNotificationCommentOnlyOnTag = $this->mapToEnumArray(
            $this->preferenceNotificationCommentOnlyOnTag
        );
    }

    /**
     * @param NotificationChannel[] $channels
     */
    public function hasNotificationChannel(array $channels, NotificationChannel $channel): bool
    {
        return in_array($channel, $channels, true);
    }

    public function hasSlackCredentials(): bool
    {
        return $this->slackBotToken !== null && $this->slackMemberId !== null;
    }

    /**
     * @param array<string|NotificationChannel> $values
     *
     * @return NotificationChannel[]
     */
    private function mapToEnumArray(array $values): array
    {
        return array_map(
            static fn (string|NotificationChannel $v): NotificationChannel => $v instanceof NotificationChannel ? $v : NotificationChannel::from(
                $v
            ),
            $values,
        );
    }
}
