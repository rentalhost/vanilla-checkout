<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Checkout\Utils;

class Request
{
    public const
        REQUEST_BODY = 'Request.Body',
        REQUEST_HEADERS = 'Request.Headers';

    public static function getRequestBody(): string
    {
        return DataProvider::get(self::REQUEST_BODY, static fn() => file_get_contents('php://input'));
    }

    public static function getRequestHeaders(): array
    {
        return DataProvider::get(self::REQUEST_HEADERS, static fn() => getallheaders());
    }
}
