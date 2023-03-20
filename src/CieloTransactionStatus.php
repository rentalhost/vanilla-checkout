<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Cielo;

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
}
