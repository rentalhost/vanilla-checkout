<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Cielo\Tests\Services;

use Dotenv\Dotenv;

class Bootstrap
{
    public static function init(): void
    {
        $dotenv = Dotenv::createImmutable(getcwd());
        $dotenv->load();
    }
}

Bootstrap::init();