<?php

declare(strict_types=1);

namespace App\Repository\Jira;

use JiraCloud\Issue\Priority;
use JiraCloud\Priority\PriorityService;

class PriorityRepository
{
    private PriorityService $service;

    public function __construct()
    {
        $this->service = new PriorityService();
    }

    /**
     * @return Priority[]
     */
    public function getAll(): array
    {
        return $this->service->getAll();
    }
}
