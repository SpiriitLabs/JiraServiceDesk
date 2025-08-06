<?php

namespace App\Entity;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
class UserNotificationPreferences
{
    #[ORM\Column(type: Types::BOOLEAN)]
    public bool $global = true;

    #[ORM\Column(type: Types::BOOLEAN)]
    public bool $issueCreated = true;

    #[ORM\Column(type: Types::BOOLEAN)]
    public bool $issueUpdated = true;

    #[ORM\Column(type: Types::BOOLEAN)]
    public bool $commentCreated = true;

    #[ORM\Column(type: Types::BOOLEAN)]
    public bool $commentUpdated = true;

    #[ORM\Column(type: Types::BOOLEAN)]
    public bool $commentOnlyOnTag = true;
}
