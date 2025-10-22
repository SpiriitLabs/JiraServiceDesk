<?php

declare(strict_types=1);

namespace App\Model\Filter;

use App\Entity\User;
use App\Model\Filter\Trait\FilterQueryTrait;

class ProjectFilter
{
    use FilterQueryTrait;

    public function __construct(
        public User $user,
        ?string $query = null
    ) {
        $this->query = $query;
    }
}
