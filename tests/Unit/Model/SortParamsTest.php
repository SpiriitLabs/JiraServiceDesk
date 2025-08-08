<?php

namespace App\Tests\Unit\Model;

use App\Model\SortParams;
use PHPUnit\Framework\TestCase;

class SortParamsTest extends TestCase
{
    public function testItMustReturnWithCreateMethod(): void
    {
        $sortParams = SortParams::createSort(sort: 'name');

        self::assertSame('name', $sortParams->by);
        self::assertSame('asc', $sortParams->dir);

        $sortParams = SortParams::createSort(sort: '-name');

        self::assertSame('name', $sortParams->by);
        self::assertSame('desc', $sortParams->dir);
    }
}
