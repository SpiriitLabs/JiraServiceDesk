<?php

namespace App\Exception\Project;

class ProjectAlreadyExistException extends \Exception
{
    public function __construct()
    {
        parent::__construct('project.flashes.alreadyExist');
    }
}
