<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Cielo;

class CieloUtils
{
    public static function getRequestBody(): string
    {
        return CieloTesting::get('requestBody', file_get_contents('php://input'));
    }

    public static function getRequestHeaders(): array
    {
        return CieloTesting::get('requestHeaders', getallheaders());
    }
}
