<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Checkout\Wrapper\Cielo;

class CieloTransactionNotification
{
    public string $id;

    public string $orderNumber;

    public int $paymentInstallments;

    public CieloTransactionStatus $paymentStatus;

    private function __construct()
    {
    }

    public static function fromNotification(array $notification): self
    {
        $instance = new self();

        $instance->id                  = $notification['checkout_cielo_order_number'];
        $instance->orderNumber         = $notification['order_number'];
        $instance->paymentStatus       = CieloTransactionStatus::from($notification['payment_status']);
        $instance->paymentInstallments = $notification['payment_installments'];

        return $instance;
    }
}
