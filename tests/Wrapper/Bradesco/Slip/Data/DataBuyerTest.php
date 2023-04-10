<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Checkout\Tests\Wrapper\Bradesco\Slip\Data;

use PHPUnit\Framework\TestCase;
use Rentalhost\Vanilla\Checkout\Wrapper\Bradesco\Slip\Data\DataBuyer;

class DataBuyerTest
    extends TestCase
{
    public function testExceptionInvalidIPv4AddressFormat()
    {
        $this->expectExceptionMessage('invalid IPv4 address format');

        new DataBuyer('Buyer', '12345678910', '0.0.0');
    }

    public function testInvalidDocumentFormat()
    {
        $this->expectExceptionMessage('invalid document format');

        new DataBuyer('Buyer', '123');
    }

    public function testValidBuyer()
    {
        $shop = new DataBuyer('Buyer', '12345678910');

        $this->assertSame('Buyer', $shop->name);
        $this->assertSame('12345678910', $shop->document);
        $this->assertSame(null, $shop->ipAddress);
        $this->assertSame(null, $shop->userAgent);

        $shop = new DataBuyer('Buyer', '12345678910', '255.255.255.255');

        $this->assertSame('255.255.255.255', $shop->ipAddress);
        $this->assertSame(null, $shop->userAgent);

        $shop = new DataBuyer('Buyer', '12345678910', '0.0.0.0', 'Chrome XYZ');

        $this->assertSame('0.0.0.0', $shop->ipAddress);
        $this->assertSame('Chrome XYZ', $shop->userAgent);

        $shop = new DataBuyer('Buyer', '12345678910', null, 'Chrome XYZ');

        $this->assertSame(null, $shop->ipAddress);
        $this->assertSame('Chrome XYZ', $shop->userAgent);
    }
}
