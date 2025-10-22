<?php

declare(strict_types=1);

namespace App\Controller\BrowseIssue;

use App\Controller\App\Project\AbstractController;
use App\Controller\App\Project\Issue\RouteCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;

class BrowseController extends AbstractController
{
    public function __invoke(
        Request $request,
        string $keyIssue,
        #[MapQueryParameter]
        ?string $focusedCommentId = null,
    ): Response {
        if (! preg_match('/^[A-Za-z0-9]+-\d+$/', $keyIssue)) {
            throw new \InvalidArgumentException(message: 'Invalid issue key format');
        }
        [$projectKey, $issueId] = explode('-', $keyIssue);

        return $this->redirectToRoute(
            route: RouteCollection::VIEW->prefixed(),
            parameters: [
                'key' => $projectKey,
                'keyIssue' => $keyIssue,
                'focusedCommentId' => $focusedCommentId,
            ]
        );
    }
}
