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
        DataProvider::put(Request::REQUEST_HEADERS, [ 'MerchantId' => 'mock' ]);
        DataProvider::put(Request::REQUEST_BODY, json_encode([
            'checkout_cielo_order_number' => '123-456-guid',
            'order_number'                => 'Order01',
            'payment_status'              => CieloTransactionStatus::PAID->value,
            'payment_installments'        => 3,
            'product_id'                  => 'uuid-value',
        ]));

        $listener     = new CieloListener('mock');
        $notification = $listener->getTransactionNotification();

        $this->assertSame('123-456-guid', $notification->id);
        $this->assertSame('Order01', $notification->orderNumber);
        $this->assertSame(CieloTransactionStatus::PAID, $notification->paymentStatus);
        $this->assertTrue($notification->paymentStatus->isPaid());
        $this->assertFalse($notification->paymentStatus->isRejected());
        $this->assertSame(3, $notification->paymentInstallments);
        $this->assertSame('uuid-value', $notification->productId);

        DataProvider::put(Request::REQUEST_BODY, json_encode([
            'checkout_cielo_order_number' => '123-456-guid',
            'order_number'                => 'Order01',
            'payment_status'              => CieloTransactionStatus::AUTHORIZED->value,
            'payment_installments'        => 3,
            'product_id'                  => 'uuid-value',
        ]));

        $listener     = new CieloListener('mock');
        $notification = $listener->getTransactionNotification();

        $this->assertSame(CieloTransactionStatus::AUTHORIZED, $notification->paymentStatus);
        $this->assertTrue($notification->paymentStatus->isPaid());

        DataProvider::put(Request::REQUEST_BODY, json_encode([
            'checkout_cielo_order_number' => '123-456-guid',
            'order_number'                => 'Order01',
            'payment_status'              => CieloTransactionStatus::DENIED->value,
            'payment_installments'        => 3,
            'product_id'                  => 'uuid-value',
        ]));

        $listener     = new CieloListener('mock');
        $notification = $listener->getTransactionNotification();

        $this->assertSame(CieloTransactionStatus::DENIED, $notification->paymentStatus);
        $this->assertFalse($notification->paymentStatus->isPaid());
        $this->assertTrue($notification->paymentStatus->isRejected());

        DataProvider::put(Request::REQUEST_BODY, json_encode([
            'checkout_cielo_order_number' => '123-456-guid',
            'order_number'                => 'Order01',
            'payment_status'              => CieloTransactionStatus::EXPIRED->value,
            'payment_installments'        => 3,
            'product_id'                  => 'uuid-value',
        ]));

        $listener     = new CieloListener('mock');
        $notification = $listener->getTransactionNotification();

        $this->assertSame(CieloTransactionStatus::EXPIRED, $notification->paymentStatus);
        $this->assertFalse($notification->paymentStatus->isPaid());
        $this->assertTrue($notification->paymentStatus->isRejected());

        DataProvider::put(Request::REQUEST_BODY, json_encode([
            'checkout_cielo_order_number' => '123-456-guid',
            'order_number'                => 'Order01',
            'payment_status'              => CieloTransactionStatus::VOIDED->value,
            'payment_installments'        => 3,
            'product_id'                  => 'uuid-value',
        ]));

        $listener     = new CieloListener('mock');
        $notification = $listener->getTransactionNotification();

        $this->assertSame(CieloTransactionStatus::VOIDED, $notification->paymentStatus);
        $this->assertFalse($notification->paymentStatus->isPaid());
        $this->assertTrue($notification->paymentStatus->isRejected());

        DataProvider::put(Request::REQUEST_BODY, json_encode([
            'checkout_cielo_order_number' => '123-456-guid',
            'order_number'                => 'Order01',
            'payment_status'              => CieloTransactionStatus::NOT_FINALIZED->value,
            'payment_installments'        => 3,
            'product_id'                  => 'uuid-value',
        ]));

        $listener     = new CieloListener('mock');
        $notification = $listener->getTransactionNotification();

        $this->assertSame(CieloTransactionStatus::NOT_FINALIZED, $notification->paymentStatus);
        $this->assertFalse($notification->paymentStatus->isPaid());
        $this->assertTrue($notification->paymentStatus->isRejected());
    }

    public function testListenerException()
    {
        $this->expectExceptionMessage('invalid Merchant ID header');

        $listener = new CieloListener('example');
        $listener->getTransactionNotification();
    }

    public function testListenerException2()
    {
        $this->expectExceptionMessage('invalid Merchant ID header');

        DataProvider::put(Request::REQUEST_HEADERS, [ 'MerchantId' => 'mock' ]);

        $listener = new CieloListener('example');
        $listener->getTransactionNotification();
    }

    public function testListenerWithHeaderCheckDisabled()
    {
        DataProvider::put(Request::REQUEST_HEADERS, [ 'MerchantId' => 'unexpected-token' ]);
        DataProvider::put(Request::REQUEST_BODY, json_encode([
            'checkout_cielo_order_number' => '123-456-guid',
            'order_number'                => 'Order01',
            'payment_status'              => CieloTransactionStatus::PAID->value,
            'payment_installments'        => 3,
            'product_id'                  => 'uuid-value',
        ]));

        $listener = new CieloListener('mock');
        $listener->enableHeaderCheck(false);

        $notification = $listener->getTransactionNotification();

        $this->assertSame('123-456-guid', $notification->id);
    }
}
