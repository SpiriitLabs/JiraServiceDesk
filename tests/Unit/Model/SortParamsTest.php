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
        self::assertSame('ascname', $sortParams->__toString());

        $sortParams = SortParams::createSort(sort: '-name');

        self::assertSame('name', $sortParams->by);
        self::assertSame('desc', $sortParams->dir);
        self::assertSame('descname', $sortParams->__toString());

        $sortParams = new SortParams(by: 'created', dir: '-');

        self::assertSame('created', $sortParams->by);
        self::assertSame('-', $sortParams->dir);
        self::assertSame('-created', $sortParams->__toString());
    }
}
