<?php

declare(strict_types=1);

namespace App\Repository\Jira;

use JiraCloud\Label\LabelSearchResult;
use JiraCloud\Label\LabelService;

class LabelRepository
{
    private LabelService $service;

    public function __construct()
    {
        $this->service = new LabelService();
    }

    public function getAll(): LabelSearchResult
    {
        return $this->service->getAllLabels();
    }
}
