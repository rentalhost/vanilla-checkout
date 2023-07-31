<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Checkout\Wrapper\Bradesco\Slip;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use JetBrains\PhpStorm\ArrayShape;

class Bradesco
{
    private readonly Client $client;

    private readonly string $merchantId;

    private readonly string $merchantKey;

    public function __construct(
        #[ArrayShape([
            'merchantId'  => 'string',
            'merchantKey' => 'string',
            'handler'     => MockHandler::class,
        ])]
        array $options = []
    ) {
        $this->merchantId  = $options['merchantId'];
        $this->merchantKey = $options['merchantKey'];

        $this->client = new Client([
            'handler' => $options['handler'] ?? null,
        ]);
    }

    private static function getEndpoint(): string
    {
        return 'https://meiosdepagamentobradesco.com.br/apiboleto';
    }

    public function createBillet(BradescoSlipRequest $productSlip): BradescoSlipResponse
    {
        $request = $this->client->request('POST', self::getEndpoint() . '/transacao', [
            'headers' => [
                'Authorization' => $this->getAuthorization(),
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
            ],
            'body'    => json_encode($productSlip->toTransactionArray($this->merchantId)),
        ]);

        return new BradescoSlipResponse(json_decode($request->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR));
    }

    /**
     * Get a new Authorization header token.
     */
    public function getAuthorization(): string|null
    {
        return sprintf('Basic %s', base64_encode($this->merchantId . ':' . $this->merchantKey));
    }
}
