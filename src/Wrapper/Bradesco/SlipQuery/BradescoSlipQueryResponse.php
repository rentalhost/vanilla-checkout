<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Checkout\Wrapper\Bradesco\SlipQuery;

use DateTime;
use JetBrains\PhpStorm\ArrayShape;

class BradescoSlipQueryResponse
{
    public DateTime $date;

    public DateTime $datePaid;

    public int $error;

    public string $reference;

    public BradescoSlipQueryResponseStatus $status;

    public float $value;

    public float $valuePaid;

    public function __construct(
        #[ArrayShape([
            'numero'        => 'string',
            'valor'         => 'string',
            'valorPago'     => 'string',
            'data'          => 'string',
            'dataPagamento' => 'string',
            'status'        => 'string',
            'erro'          => 'string',
        ])]
        public array $response) {
        $this->reference = $response['numero'];
        $this->value     = $response['valor'] / 100;
        $this->valuePaid = $response['valorPago'] / 100;
        $this->date      = DateTime::createFromFormat('d/m/Y H:i:s', $this->response['data']);
        $this->datePaid  = DateTime::createFromFormat('d/m/Y H:i:s', $this->response['dataPagamento']);
        $this->status    = BradescoSlipQueryResponseStatus::from((int) $response['status']);
        $this->error     = (int) $response['erro'];
    }

    public function isPaidEqual(): bool
    {
        return $this->status === BradescoSlipQueryResponseStatus::PAID_EQUAL;
    }
}
