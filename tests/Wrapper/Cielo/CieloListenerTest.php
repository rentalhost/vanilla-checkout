<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Checkout\Tests\Wrapper\Cielo;

use PHPUnit\Framework\TestCase;
use Rentalhost\Vanilla\Checkout\Utils\DataProvider;
use Rentalhost\Vanilla\Checkout\Utils\Request;
use Rentalhost\Vanilla\Checkout\Wrapper\Cielo\CieloListener;
use Rentalhost\Vanilla\Checkout\Wrapper\Cielo\CieloTransactionStatus;

class CieloListenerTest
    extends TestCase
{
    protected function setUp(): void
    {
        DataProvider::clear();
    }

    public function testListener()
    {
        DataProvider::put(Request::REQUEST_HEADERS, [ 'MerchantId' => 'example' ]);
        DataProvider::put(Request::REQUEST_BODY, json_encode([
            'checkout_cielo_order_number' => '123-456-guid',
            'order_number'                => 'Order01',
            'payment_status'              => CieloTransactionStatus::PAID->value,
            'payment_installments'        => 3,
        ]));

        $listener     = new CieloListener('example');
        $notification = $listener->getTransactionNotification();

        $this->assertSame('123-456-guid', $notification->id);
        $this->assertSame('Order01', $notification->orderNumber);
        $this->assertSame(CieloTransactionStatus::PAID, $notification->paymentStatus);
        $this->assertTrue($notification->paymentStatus->isPaid());
        $this->assertFalse($notification->paymentStatus->isRejected());
        $this->assertSame(3, $notification->paymentInstallments);
    }

    public function testListenerException()
    {
        $this->expectExceptionMessage('invalid Merchant ID header');

        $listener = new CieloListener('example');
        $listener->getTransactionNotification();
    }
}
