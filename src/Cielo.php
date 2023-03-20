<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Cielo;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Handler\MockHandler;
use JetBrains\PhpStorm\ArrayShape;

class Cielo
{
    private string|null $authorizationBearer = null;

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
        return 'https://cieloecommerce.cielo.com.br/api/public';
    }

    public function createLink(CieloProductLink $productLink): CieloProductLinkResponse
    {
        $request = $this->client->request('POST', self::getEndpoint() . '/v1/products/', [
            'headers' => [
                'Authorization' => $this->getAuthorization(),
                'Accept'        => 'application/json',
                'Content-Type'  => 'application/json',
            ],
            'body'    => json_encode([
                'name'                    => $productLink->proudctName,
                'description'             => $productLink->productDescription,
                'showDescription'         => $productLink->productDescription !== null,
                'price'                   => (int) ($productLink->productPrice * 100),
                'expirationDate'          => $productLink->paymentExpirationDate?->format('Y-m-d'),
                'weight'                  => $productLink->productWeight,
                'softDescriptor'          => $productLink->transactionSoftDescriptor,
                'maxNumberOfInstallments' => $productLink->paymentMaxInstallments,
                'quantity'                => $productLink->transactionsQuantity,
                'type'                    => $productLink->transactionType->value,
            ]),
        ]);

        $response = json_decode($request->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

        return new CieloProductLinkResponse(
            $productLink,
            $response['id'],
            $response['shortUrl']
        );
    }

    /**
     * Get a new Authorization header token.
     */
    public function getAuthorization(): string|null
    {
        if (!$this->authorizationBearer) {
            try {
                $request = $this->client->request('POST', self::getEndpoint() . '/v2/token?grant_type=client_credentials', [
                    'headers' => [
                        'Authorization' => 'Basic ' . base64_encode($this->merchantId . ':' . $this->merchantKey),
                        'Accept'        => 'application/json',
                        'Content-Type'  => 'application/json',
                    ],
                ]);

                $response = json_decode($request->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

                $this->authorizationBearer = sprintf('Bearer %s', $response['access_token']);
            }
            catch (ClientException) {
                return null;
            }
        }

        return $this->authorizationBearer;
    }
}
