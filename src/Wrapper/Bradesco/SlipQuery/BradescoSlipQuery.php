<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Checkout\Wrapper\Bradesco\SlipQuery;

use DateInterval;
use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use JetBrains\PhpStorm\ArrayShape;

class BradescoSlipQuery
{
    private string|null $authorizationToken = null;

    private readonly Client $client;

    private readonly string $merchantId;

    private readonly string $merchantKey;

    private readonly string $merchantUsername;

    public function __construct(
        #[ArrayShape([
            'merchantId'       => 'string',
            'merchantKey'      => 'string',
            'merchantUsername' => 'string',
            'handler'          => MockHandler::class,
        ])]
        array $options = []
    ) {
        $this->merchantId       = $options['merchantId'];
        $this->merchantKey      = $options['merchantKey'];
        $this->merchantUsername = $options['merchantUsername'];

        $this->client = new Client([
            'handler' => $options['handler'] ?? null,
        ]);
    }

    private static function getEndpoint(): string
    {
        return 'https://meiosdepagamentobradesco.com.br/SPSConsulta';
    }

    /**
     * Get a new Authorization header token.
     */
    public function getAuthorizationHeader(): string|null
    {
        return sprintf('Basic %s', base64_encode($this->merchantUsername . ':' . $this->merchantKey));
    }

    /**
     * Get a new Authorization token.
     */
    public function getAuthorizationToken(): string|null
    {
        if ($this->authorizationToken === null) {
            $this->authorizationToken = json_decode($this->client->request('GET', sprintf(self::getEndpoint() . '/Authentication/%s', $this->merchantId), [
                'headers' => [
                    'Authorization' => $this->getAuthorizationHeader(),
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                ],
            ])->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR)['token']['token'];
        }

        return $this->authorizationToken;
    }

    /** @return BradescoSlipQueryResponse[] */
    public function query(): array
    {
        $token   = $this->getAuthorizationToken();
        $offset  = 1;
        $results = [];

        while ($offset >= 1) {
            $request = $this->client->request('GET', sprintf(self::getEndpoint() . '/GetOrderListPayment/%s/boleto', $this->merchantId), [
                'headers' => [
                    'Authorization' => $this->getAuthorizationHeader(),
                    'Accept'        => 'application/json',
                    'Content-Type'  => 'application/json',
                ],
                'query'   => [
                    'token'       => $token,
                    'offset'      => $offset,
                    'dataInicial' => (new DateTime())->sub(new DateInterval('P5D')),
                    'dataFinal'   => new DateTime(),
                    'limit'       => 1500,
                    'status'      => 1,
                ],
            ]);

            $response = json_decode($request->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

            $offset    = $response['paging']['nextOffset'];
            $results[] = $response['pedidos'];
        }

        return array_map(static fn(array $result) => new BradescoSlipQueryResponse($result), array_merge(...$results));
    }
}
