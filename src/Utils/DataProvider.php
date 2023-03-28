<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Checkout\Utils;

class DataProvider
{
    private static array $properties = [];

    public static function clear(): void
    {
        self::$properties = [];
    }

    public static function get(string $key, mixed $else = null)
    {
        if (array_key_exists($key, self::$properties)) {
            return self::$properties[$key];
        }

        if (is_callable($else)) {
            return $else();
        }

        return $else;
    }

    public static function put(string $key, mixed $value): void
    {
        self::$properties[$key] = $value;
    }
}
