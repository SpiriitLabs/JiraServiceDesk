<?php

declare(strict_types=1);

namespace App\Exception\User;

class CurrentPasswordWrongException extends \Exception
{
    public function __construct()
    {
        parent::__construct('user.flashes.currentPasswordWrong');
    }
}
