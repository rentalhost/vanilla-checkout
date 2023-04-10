<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Checkout\Wrapper\Bradesco\Slip\Data;

use Exception;

class DataShop
{
    public function __construct(
        /** Shop name. */
        public string $name,

        /** Shop description. */
        public string $description,

        /** Shop wallet number. */
        public string $wallet,

        /** Shop logo URL. */
        public string|null $logoURL = null
    ) {
        $this->name = substr($this->name, 0, 150);

        if (strlen($this->wallet) !== 2) {
            throw new Exception('invalid wallet number');
        }

        $this->description = substr($this->description, 0, 200);

        if ($this->logoURL && strlen($this->logoURL) > 200) {
            throw new Exception('logo URL too long');
        }
    }
}
