<?php

declare(strict_types=1);

namespace App\Message\Query;

class GetEntityById
{
    public string $class;

    public int $id;

    public function __construct(
        string $class,
        int $id
    ) {
        $this->class = $class;
        $this->id = $id;
    }
}
