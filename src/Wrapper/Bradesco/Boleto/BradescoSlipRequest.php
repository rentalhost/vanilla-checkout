<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Checkout\Wrapper\Bradesco\Boleto;

use DateTime;
use Exception;
use JetBrains\PhpStorm\ArrayShape;
use Rentalhost\Vanilla\Checkout\Wrapper\Bradesco\Boleto\Data\DataAddress;
use Rentalhost\Vanilla\Checkout\Wrapper\Bradesco\Boleto\Data\DataBuyer;
use Rentalhost\Vanilla\Checkout\Wrapper\Bradesco\Boleto\Data\DataShop;

class BradescoSlipRequest
{
    public function __construct(
        /** Request reference. */
        public string $requestReference,

        /** Request number with 11 digits. */
        public int $requestNumber,

        /** Product price (15.0 === R$ 15.00). */
        public float $productPrice,

        /** Product description. */
        public string $productDescription,

        /** Product instructions (max. 12 lines). */
        public string|null $productInstructions,

        /** Shop. */
        public DataShop $shop,

        /** Buyer. */
        public DataBuyer $buyer,

        /** Buyer address. */
        public DataAddress $buyerAddress,

        /** Slip expiration date. */
        public DateTime $dateExpiration,
    ) {
        if (strlen($this->requestReference) > 27 ||
            preg_match('/^[\w.]?\d+[\w.-]*$/', $this->requestReference) === 0) {
            throw new Exception('invalid request reference');
        }

        if ($this->requestNumber < 1000) {
            throw new Exception('request number must be greater or equal to 1000');
        }

        if ($this->requestNumber > 99999999999) {
            throw new Exception('request number must be lower or equal to 99999999999');
        }
    }

    /** @return string[] */
    public function getInstructions(): array
    {
        if ($this->productInstructions === null ||
            $this->productInstructions === '') {
            return [];
        }

        $instructions = [];

        foreach (preg_split('/\\r?\\n/', $this->productInstructions) as $line) {
            $instructions[] = str_split($line, 60);
        }

        return array_slice(array_merge(...$instructions), 0, 12);
    }

    #[ArrayShape([
        'merchant_id'                         => 'string',
        'meio_pagamento'                      => 'int',
        'pedido'                              => [ 'numero' => 'string', 'valor' => 'int', 'descricao' => 'string', ],
        'comprador'                           => [
            'nome'       => 'string',
            'documento'  => 'string',
            'ip'         => 'string|null',
            'user_agent' => 'string|null',
            'endereco'   => [
                'cep'         => 'string',
                'logradouro'  => 'string',
                'numero'      => 'string',
                'complemento' => 'string|null',
                'bairro'      => 'string',
                'cidade'      => 'string',
                'uf'          => 'string',
            ],
        ],
        'boleto'                              => [
            'beneficiario'       => 'string',
            'carteira'           => 'string',
            'nosso_numero'       => 'string',
            'data_emissao'       => 'string',
            'data_vencimento'    => 'string',
            'valor_titulo'       => 'int',
            'url_logotipo'       => 'string|null',
            'mensagem_cabecalho' => 'string',
            'tipo_renderizacao'  => 'int',
            'instrucoes'         => 'string[]',
        ],
        'token_request_confirmacao_pagamento' => 'string',
    ])]
    public function toTransactionArray(string $merchantId): array
    {
        $instructions = $this->getInstructions();

        return [
            'merchant_id'                         => $merchantId,
            'meio_pagamento'                      => 300,

            // Request.
            'pedido'                              => [
                'numero'    => $this->requestReference,
                'valor'     => (int) ($this->productPrice * 100),
                'descricao' => substr($this->productDescription, 0, 255),
            ],

            // Buyer.
            'comprador'                           => array_filter([
                'nome'       => $this->buyer->name,
                'documento'  => $this->buyer->document,
                'ip'         => $this->buyer->ipAddress,
                'user_agent' => $this->buyer->userAgent,

                'endereco' => [
                    'cep'         => $this->buyerAddress->cep,
                    'logradouro'  => $this->buyerAddress->street,
                    'numero'      => $this->buyerAddress->number,
                    'complemento' => $this->buyerAddress->complement,
                    'bairro'      => $this->buyerAddress->district,
                    'cidade'      => $this->buyerAddress->city,
                    'uf'          => $this->buyerAddress->uf,
                ],
            ]),

            // Slip.
            'boleto'                              => [
                'beneficiario'       => $this->shop->name,
                'carteira'           => $this->shop->wallet,
                'nosso_numero'       => $this->requestNumber,
                'data_emissao'       => (new DateTime())->format('Y-m-d'),
                'data_vencimento'    => $this->dateExpiration->format('Y-m-d'),
                'valor_titulo'       => (int) ($this->productPrice * 100),
                'url_logotipo'       => $this->shop->logoURL,
                'mensagem_cabecalho' => $this->shop->description,
                'tipo_renderizacao'  => 2,
                'instrucoes'         => array_combine(
                    array_map(static fn(int $key) => sprintf('instrucao_linha_%u', $key + 1), array_keys($instructions)),
                    $instructions
                ),
            ],

            // Token.
            'token_request_confirmacao_pagamento' => hash('sha256', random_bytes(4096)),
        ];
    }
}
