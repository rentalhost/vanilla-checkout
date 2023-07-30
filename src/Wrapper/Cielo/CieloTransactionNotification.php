<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Checkout\Wrapper\Cielo;

class CieloTransactionNotification
{
    public string $id;

    public string $orderNumber;

    public int $paymentInstallments;

    public CieloTransactionStatus $paymentStatus;

    public string|null $productId;

    private function __construct()
    {
    }

    public static function fromNotification(array $notification): self
    {
        $instance = new self();

        $instance->id                  = $notification['checkout_cielo_order_number'];
        $instance->orderNumber         = $notification['order_number'];
        $instance->paymentStatus       = CieloTransactionStatus::from((int) $notification['payment_status']);
        $instance->paymentInstallments = (int) ($notification['payment_installments'] ?? 1);
        $instance->productId           = $notification['product_id'] ?? null;

        return $instance;
    }
}
