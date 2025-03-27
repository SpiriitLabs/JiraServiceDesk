<?php

namespace App\Controller\App\Issue;

use App\Form\App\Issue\IssueCommentFormType;
use App\Formatter\Jira\IssueAttachmentFormatter;
use App\Message\Command\App\Issue\CreateComment;
use App\Repository\Jira\IssueRepository;
use DH\Adf\Exporter\Html\Block\DocumentExporter;
use DH\Adf\Node\Block\Document;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    path: '/issue/{key}',
    name: RouteCollection::VIEW->value,
    methods: [Request::METHOD_GET, Request::METHOD_POST],
)]
class ViewController extends AbstractController
{

    public function __construct(
        private readonly IssueRepository $issueRepository,
        private readonly IssueAttachmentFormatter $issueAttachmentFormatter,
    ) {
    }

    public function __invoke(
        string $key,
        Request $request,
    ): Response {
        $issue = $this->issueRepository->getFull($key);
        $issue = $this->issueAttachmentFormatter->format($issue);
        $comments = $this->issueRepository->getCommentForIssue($key);

        $form = $this->createForm(IssueCommentFormType::class, new CreateComment($issue, '', []));
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            dd($form->getData());
        }

        return $this->render(
            view: 'app/issue/view.html.twig',
            parameters: [
                'key' => $key,
                'issue' => $issue,
                'comments' => $comments->comments,
                'commentForm' => $form->createView(),
            ]
        );
    }

}
