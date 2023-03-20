<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Cielo\Tests;

use PHPUnit\Framework\TestCase;
use Rentalhost\Vanilla\Cielo\CieloListener;
use Rentalhost\Vanilla\Cielo\CieloTesting;
use Rentalhost\Vanilla\Cielo\CieloTransactionStatus;

class CieloListenerTest
    extends TestCase
{
    protected function setUp(): void
    {
        CieloTesting::clear();
    }

    public function testListener()
    {
        CieloTesting::put('requestHeaders', [ 'MerchantId' => 'example' ]);
        CieloTesting::put('requestBody', json_encode([
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
        $this->assertSame(3, $notification->paymentInstallments);
    }

    public function testListenerException()
    {
        $this->expectExceptionMessage('invalid Merchant ID header');

        $listener = new CieloListener('example');
        $listener->getTransactionNotification();
    }
}
