<?php

namespace App\Model;

class SortParams
{
    public const string DIR_DESC = 'desc';

    public const string DIR_ASC = 'asc';

    public string $by;

    public string $dir;

    public function __construct(string $by, string $dir)
    {
        $this->by = $by;
        $this->dir = $dir;
    }

    public static function createSort(string $sort): self
    {
        if (str_starts_with($sort, '-')) {
            return new self(substr($sort, 1), self::DIR_DESC);
        }

        return new self($sort, self::DIR_ASC);
    }

    public function __toString()
    {
        return $this->dir . $this->by;
    }
}
