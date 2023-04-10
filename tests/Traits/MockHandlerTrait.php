<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Checkout\Tests\Traits;

use GuzzleHttp\Handler\MockHandler;

trait MockHandlerTrait
{
    public function testMockHandler(): MockHandler
    {
        $this->assertSame(true, true);

        return new MockHandler();
    }
}
