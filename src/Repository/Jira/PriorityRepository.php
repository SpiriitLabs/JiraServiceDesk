<?php

namespace App\Repository\Jira;

use JiraCloud\JiraException;
use JiraCloud\Priority\PriorityService;

class PriorityRepository
{
    private PriorityService $service;

    public function __construct()
    {
        $this->service = new PriorityService();
    }

    public function getAll(): array
    {
        try {
            return $this->service->getAll();
        } catch (JiraException $exception) {
            return [];
        }
    }
}
