<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Checkout\Tests\Wrapper\Bradesco\Slip\Data;

use PHPUnit\Framework\TestCase;
use Rentalhost\Vanilla\Checkout\Wrapper\Bradesco\Slip\Data\DataAddress;

class DataAddressTest
    extends TestCase
{
    public function testExceptionInvalidCEPFormat()
    {
        $this->expectExceptionMessage('invalid CEP format');

        new DataAddress('12345', 'Street', '123', '456', 'District', 'City', 'RJ');
    }

    public function testExceptionInvalidUF()
    {
        $this->expectExceptionMessage('invalid UF');

        new DataAddress('12345789', 'Street', '123', '456', 'District', 'City', 'XX');
    }

    public function testExceptionInvalidUF2()
    {
        $this->expectExceptionMessage('invalid UF');

        new DataAddress('12345789', 'Street', '123', null, 'District', 'City', 'XX');
    }

    public function testValidAddress()
    {
        $address = new DataAddress('12345678', 'Street', '123', '456', 'District', 'City', 'RJ');

        $this->assertSame('12345678', $address->cep);
        $this->assertSame('Street', $address->street);
        $this->assertSame('123', $address->number);
        $this->assertSame('456', $address->complement);
        $this->assertSame('District', $address->district);
        $this->assertSame('City', $address->city);
        $this->assertSame('RJ', $address->uf);

        $address = new DataAddress('12345678', 'Street', '123', null, 'District', 'City', 'RJ');

        $this->assertSame(null, $address->complement);
    }
}
