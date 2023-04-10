<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Checkout\Wrapper\Bradesco\SlipQuery;

enum BradescoSlipQueryResponseStatus: int
{
    case GENERATED_10 = 10;
    case GENERATED_13 = 13;
    case GENERATED_14 = 14;
    case GENERATED_15 = 15;

    case PAID_EQUAL = 21;
    case PAID_LOWER = 22;
    case PAID_GREATER = 23;
}
