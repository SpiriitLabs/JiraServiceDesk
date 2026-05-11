<?php

declare(strict_types=1);

namespace App\Controller\App\Project\Issue;

use App\Controller\App\Project\AbstractController;
use App\Controller\App\Project\Issue\RouteCollection as IssueRouteCollection;
use App\Controller\Common\CreateControllerTrait;
use App\Entity\Project;
use App\Entity\User;
use App\Form\App\Issue\CreateIssueFormType;
use App\Message\Command\App\Issue\CreateIssue;
use JiraCloud\JiraException;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(
    path: '/project/{key}/issues/create',
    name: IssueRouteCollection::CREATE->value,
    methods: [Request::METHOD_GET, Request::METHOD_POST],
)]
class CreateController extends AbstractController
{
    use CreateControllerTrait;

    public function __invoke(
        Request $request,
        #[MapEntity(mapping: [
            'key' => 'jiraKey',
        ])]
        Project $project,
        #[CurrentUser]
        User $user,
    ): Response {
        $this->setCurrentProject($project);

        $form = $this->createForm(
            type: CreateIssueFormType::class,
            data: new CreateIssue(
                project: $project,
                creator: $user,
            ),
            options: [
                'referer_url' => $request->headers->get('referer'),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $refererUrl = null;
            if ($form->has('refererUrl')) {
                $refererUrl = $form->get('refererUrl')
                    ->getData()
                ;
            }

            try {
                $issue = $this->handle($form->getData());
            } catch (HandlerFailedException $exception) {
                $jiraExceptions = $exception->getWrappedExceptions(JiraException::class);
                if ($jiraExceptions !== []) {
                    $jiraException = current($jiraExceptions);
                    $response = json_decode((string) $jiraException->getResponse(), true);
                    $jiraErrors = array_merge(
                        $response['errorMessages'] ?? [],
                        array_values($response['errors'] ?? [])
                    );

                    foreach ($jiraErrors as $errorMessage) {
                        $this->addFlash(type: 'danger', message: $errorMessage);
                    }

                    if ($jiraErrors === []) {
                        $this->addFlash(type: 'danger', message: 'flash.error');
                    }
                } else {
                    $this->addFlash(type: 'danger', message: 'flash.error');
                }

                return $this->render(
                    view: 'app/project/issue/create.html.twig',
                    parameters: [
                        'project' => $project,
                        'form' => $form->createView(),
                    ],
                );
            }

            if ($issue !== null) {
                $this->addFlash(
                    type: 'success',
                    message: 'flash.created',
                );

                if ($refererUrl !== null) {
                    return $this->redirect($refererUrl);
                }

                return $this->redirectToRoute(
                    route: IssueRouteCollection::VIEW->prefixed(),
                    parameters: [
                        'key' => $project->jiraKey,
                        'keyIssue' => $issue->key,
                    ]
                );
            }

            $this->addFlash(
                type: 'danger',
                message: 'flash.error',
            );
        }

        return $this->render(
            view: 'app/project/issue/create.html.twig',
            parameters: [
                'project' => $project,
                'form' => $form->createView(),
            ],
        );
    }
}
