<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Checkout\Tests\Utils;

use PHPUnit\Framework\TestCase;
use Rentalhost\Vanilla\Checkout\Utils\DataProvider;
use Rentalhost\Vanilla\Checkout\Utils\Request;

class RequestTest
    extends TestCase
{
    public static function tearDownAfterClass(): void
    {
        DataProvider::clear();
    }

    public function testGetRequestBody()
    {
        $this->assertSame('', Request::getRequestBody());

        DataProvider::put(Request::REQUEST_BODY, '123');

        $this->assertSame('123', Request::getRequestBody());
    }

    public function testGetRequestHeaders()
    {
        $this->assertSame([], Request::getRequestHeaders());

        DataProvider::put(Request::REQUEST_HEADERS, [ 'abc' => 123 ]);

        $this->assertSame([ 'abc' => 123 ], Request::getRequestHeaders());
    }
}
