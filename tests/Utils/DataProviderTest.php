<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Checkout\Tests\Utils;

use PHPUnit\Framework\TestCase;
use Rentalhost\Vanilla\Checkout\Utils\DataProvider;

class DataProviderTest
    extends TestCase
{
    public function testDataProviderClass()
    {
        DataProvider::put('example', 123);

        $this->assertSame(123, DataProvider::get('example'));

        DataProvider::put('example', null);

        $this->assertSame(null, DataProvider::get('example', 123));

        DataProvider::clear();

        $this->assertSame(null, DataProvider::get('example'));
        $this->assertSame(123, DataProvider::get('example', 123));
        $this->assertSame(123, DataProvider::get('example', static fn() => 123));
    }
}
