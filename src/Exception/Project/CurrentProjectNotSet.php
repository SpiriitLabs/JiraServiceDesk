<?php

declare(strict_types=1);

namespace App\Exception\Project;

class CurrentProjectNotSet extends \Exception
{
    public function __construct()
    {
        parent::__construct('project.flashes.currentNotFound');
    }
}
