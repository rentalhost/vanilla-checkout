<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Checkout\Wrapper\Cielo;

enum CieloTransactionStatus: int
{
    case PENDING = 1;
    case PAID = 2;
    case DENIED = 3;
    case EXPIRED = 4;
    case VOIDED = 5;
    case NOT_FINALIZED = 6;
    case AUTHORIZED = 7;
    case CHARGEBACK = 8;

    public function isPaid(): bool
    {
        return $this === self::PAID ||
               $this === self::AUTHORIZED;
    }

    public function isRejected(): bool
    {
        return $this === self::DENIED ||
               $this === self::EXPIRED ||
               $this === self::VOIDED ||
               $this === self::NOT_FINALIZED;
    }
}
