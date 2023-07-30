<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Checkout\Wrapper\Cielo;

use Exception;
use Rentalhost\Vanilla\Checkout\Utils\Request;

class CieloListener
{
    private bool $headerCheck = true;

    public function __construct(private readonly string $merchantId)
    {
    }

    public function enableHeaderCheck(bool $headerCheck = true): void
    {
        $this->headerCheck = $headerCheck;
    }

    public function getTransactionNotification(): CieloTransactionNotification|null
    {
        if ($this->headerCheck) {
            $headers          = Request::getRequestHeaders();
            $headerMerchantId = $headers['MerchantId'] ?? null;

            if ($headerMerchantId === null ||
                $this->merchantId !== $headerMerchantId) {
                throw new Exception('invalid Merchant ID header');
            }
        }

        return CieloTransactionNotification::fromNotification(json_decode(Request::getRequestBody(), true));
    }
}
