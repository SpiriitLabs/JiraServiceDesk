<?php

declare(strict_types=1);

namespace App\Exception\Project;

class IssueLabelAlreadyExistException extends \Exception
{
    public function __construct()
    {
        parent::__construct('issueLabel.flashes.alreadyExist');
    }
}
