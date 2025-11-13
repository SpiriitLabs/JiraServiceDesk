<?php

declare(strict_types=1);

namespace App\Exception\User;

class PasswordAlreadyUseException extends \Exception
{
    public function __construct()
    {
        parent::__construct('user.flashes.alreadyUse');
    }
}
