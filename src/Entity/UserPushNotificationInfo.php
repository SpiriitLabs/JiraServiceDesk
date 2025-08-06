<?php

namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class UserPushNotificationInfo
{

    #[ORM\Column]
    public ?string $endpoint = null;

    #[ORM\Column]
    public ?string $p256dh = null;

    #[ORM\Column]
    public ?string $auth = null;

}
