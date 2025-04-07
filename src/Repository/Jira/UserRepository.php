<?php

namespace App\Repository\Jira;

use JiraCloud\JiraException;
use JiraCloud\User\UserService;

class UserRepository
{
    private UserService $service;

    public function __construct()
    {
        $this->service = new UserService();
    }

    public function getAssignableUser(string $issueKey, ?string $projectKey = null): array
    {
        try {
            $parameters = [
                'issueKey' => $issueKey,
            ];
            if ($projectKey !== null) {
                $parameters['project'] = $projectKey;
            }

            return $this->service->findAssignableUsers($parameters);
        } catch (JiraException $e) {
            return [];
        }
    }
}
