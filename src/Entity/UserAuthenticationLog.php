<?php

namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;
use Spiriit\Bundle\AuthLogBundle\Entity\AbstractAuthenticationLog;
use Spiriit\Bundle\AuthLogBundle\Entity\AuthenticableLogInterface;
use Spiriit\Bundle\AuthLogBundle\FetchUserInformation\UserInformation;

#[ORM\Entity]
#[ORM\Table(name: 'user_authentication_logs')]
class UserAuthenticationLog extends AbstractAuthenticationLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    public function __construct(
        User $user,
        UserInformation $userInformation,
    ) {
        $this->user = $user;
        parent::__construct(
            userInformation: $userInformation
        );
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): AuthenticableLogInterface
    {
        return $this->user;
    }
}
