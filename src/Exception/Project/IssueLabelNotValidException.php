<?php

declare(strict_types=1);

namespace App\Exception\Project;

class IssueLabelNotValidException extends \Exception
{
    public function __construct()
    {
        parent::__construct('issueLabel.flashes.notValid');
    }
}
