<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Checkout\Tests\Wrapper\Bradesco\Boleto\Data;

use PHPUnit\Framework\TestCase;
use Rentalhost\Vanilla\Checkout\Wrapper\Bradesco\Boleto\Data\DataShop;

class DataShopTest
    extends TestCase
{
    public function testExceptionInvalidWalletNumber()
    {
        $this->expectExceptionMessage('invalid wallet number');

        new DataShop('Shop', 'description', '');
    }

    public function testExceptionLogoURLTooLong()
    {
        $this->expectExceptionMessage('logo URL too long');

        new DataShop('Shop', 'description', '33', random_bytes(201));
    }

    public function testValidShop()
    {
        $shop = new DataShop('Shop', 'description', '33');

        $this->assertSame('Shop', $shop->name);
        $this->assertSame('description', $shop->description);
        $this->assertSame('33', $shop->wallet);
        $this->assertSame(null, $shop->logoURL);

        $shop = new DataShop('Shop', 'description', '33', 'https://...');

        $this->assertSame('https://...', $shop->logoURL);
    }
}
