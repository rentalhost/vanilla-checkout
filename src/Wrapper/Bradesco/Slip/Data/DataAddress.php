<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Checkout\Wrapper\Bradesco\Slip\Data;

use Exception;

class DataAddress
{
    public const VALID_UFS = [
        'AC',
        'AL',
        'AM',
        'AP',
        'BA',
        'CE',
        'DF',
        'ES',
        'GO',
        'MA',
        'MG',
        'MS',
        'MT',
        'PA',
        'PB',
        'PE',
        'PI',
        'PR',
        'RJ',
        'RN',
        'RO',
        'RR',
        'RS',
        'SC',
        'SE',
        'SP',
        'TO',
    ];

    public function __construct(
        /** CEP as numbers-only. */
        public string $cep,

        /** Address Street. */
        public string $street,

        /** Address number. */
        public string $number,

        /** Address complement. */
        public string|null $complement,

        /** Address district. */
        public string $district,

        /** Address city. */
        public string $city,

        /** Address UF. */
        public string $uf
    ) {
        if (preg_match('/^\d{8}$/', $this->cep) === 0) {
            throw new Exception('invalid CEP format');
        }

        $this->street = substr($this->street, 0, 70);
        $this->number = substr($this->number, 0, 10);

        if ($this->complement !== null) {
            $this->complement = substr($this->complement, 0, 20);
        }

        $this->district = substr($this->district, 0, 50);
        $this->city     = substr($this->city, 0, 50);

        $this->uf = strtoupper($this->uf);

        if (in_array($this->uf, self::VALID_UFS, true) === false) {
            throw new Exception('invalid UF');
        }
    }
}
