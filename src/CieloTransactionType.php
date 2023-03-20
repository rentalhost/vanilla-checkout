<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Cielo;

enum CieloTransactionType: string
{
    case ASSET = 'Asset';
    case DIGITAL = 'Digital';
    case SERVICE = 'Service';
    case PAYMENT = 'Payment';
    case RECURRENT = 'Recurrent';
}
