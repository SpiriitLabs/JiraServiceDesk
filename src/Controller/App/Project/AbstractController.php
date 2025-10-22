<?php

declare(strict_types=1);

namespace App\Controller\App\Project;

use App\Entity\Project;
use App\Exception\Project\CurrentProjectNotSet;
use App\Security\Voter\ProjectVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as FrameworkAbstractController;
use Symfony\Component\HttpFoundation\Response;

class AbstractController extends FrameworkAbstractController
{
    private ?Project $currentProject = null;

    public function setCurrentProject(?Project $currentProject): void
    {
        $this->denyAccessUnlessGranted(ProjectVoter::PROJECT_ACCESS, $currentProject);
        $this->currentProject = $currentProject;
    }

    public function getCurrentProject(): ?Project
    {
        return $this->currentProject;
    }

    protected function render(string $view, array $parameters = [], ?Response $response = null): Response
    {
        if ($this->currentProject == null) {
            throw new CurrentProjectNotSet();
        }

        return parent::render($view, array_merge($parameters, [
            'current_project' => $this->currentProject,
        ]), $response);
    }
}
