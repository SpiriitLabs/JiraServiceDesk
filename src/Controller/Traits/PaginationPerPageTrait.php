<?php

namespace App\Controller\Traits;

use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\Request;

trait PaginationPerPageTrait
{
    public function setCurrentPage(Pagerfanta $pagination, Request $request): void
    {
        $page = $request->query->get('page', 1);
        if ($page > $pagination->getNbPages()) {
            $page = $pagination->getNbPages();
        }
        $pagination->setCurrentPage($page);
    }
}
