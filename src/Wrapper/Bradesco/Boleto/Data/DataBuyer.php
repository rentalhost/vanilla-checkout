<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Checkout\Wrapper\Bradesco\Boleto\Data;

use Exception;

class DataBuyer
{
    public function __construct(
        /** Buyer name. */
        public string $name,

        /** Buyer document, numbers-only format: CPF/CNPJ. */
        public string $document,

        /** IPv4 IP Address. */
        public string|null $ipAddress = null,

        /** User-agent. */
        public string|null $userAgent = null
    ) {
        $this->name = substr($this->name, 0, 40);

        if (preg_match('/^\d{11,14}$/', $this->document) === 0) {
            throw new Exception('invalid document format');
        }

        if ($this->ipAddress !== null &&
            preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/', $this->ipAddress) === 0) {
            throw new Exception('invalid IPv4 address format');
        }

        if ($this->userAgent !== null) {
            $this->userAgent = substr($this->userAgent, 0, 255);
        }
    }
}
