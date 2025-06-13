<?php

namespace App\Exception\Project;

class CurrentProjectNotSet extends \Exception
{
    public function __construct()
    {
        parent::__construct('project.flashes.currentNotFound');
    }
}
