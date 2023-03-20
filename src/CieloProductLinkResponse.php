<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Cielo;

class CieloProductLinkResponse
{
    public function __construct(
        public readonly CieloProductLink $productLink,
        public readonly string $id,
        public readonly string $shortUrl)
    {
    }
}
