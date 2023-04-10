<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Checkout\Wrapper\Bradesco\Boleto;

use JetBrains\PhpStorm\ArrayShape;
use Rentalhost\Vanilla\Checkout\Wrapper\Bradesco\Boleto\Exceptions\BradescoSlipResponseException;

class BradescoSlipResponse
{
    public int $code;

    public string $message;

    public string|null $url = null;

    public function __construct(
        #[ArrayShape([
            'boleto' => [
                'url_acesso' => 'string',
            ],
            'status' => [
                'codigo'   => 'int',
                'mensagem' => 'string',
            ],
        ])]
        public array $response) {
        $this->code    = $this->response['status']['codigo'];
        $this->message = $this->response['status']['mensagem'];

        if ($this->code === 0) {
            $this->url = $this->response['boleto']['url_acesso'];
        }
        else {
            throw new BradescoSlipResponseException($this->message, $this->code);
        }
    }
}
