<?php

declare(strict_types=1);

namespace App\Message\Query;

use App\Model\SortParams;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\FormInterface;

class PaginateEntities
{
    public SortParams $sort;

    public string $class;

    public int $page;

    public int $perPage;

    public ?FormInterface $form = null;

    public ?QueryBuilder $qb = null;

    public function __construct(
        string $class,
        string $sort,
        int $page = 1,
        int $perPage = 10,
        ?FormInterface $form = null,
        ?QueryBuilder $qb = null
    ) {
        $this->class = $class;
        $this->sort = SortParams::createSort($sort);
        $this->page = $page;
        $this->perPage = $perPage;
        $this->form = $form;
        $this->qb = $qb;
    }
}
