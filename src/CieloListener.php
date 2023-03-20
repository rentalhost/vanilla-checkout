<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Cielo;

use Exception;

class CieloListener
{
    public function __construct(private readonly string $merchantId)
    {
    }

    public function getTransactionNotification(): CieloTransactionNotification|null
    {
        $headers          = CieloUtils::getRequestHeaders();
        $headerMerchantId = $headers['MerchantId'] ?? null;

        if ($headerMerchantId === null ||
            $this->merchantId !== $headerMerchantId) {
            throw new Exception('invalid Merchant ID header');
        }

        return CieloTransactionNotification::fromNotification(json_decode(CieloUtils::getRequestBody(), true));
    }
}
