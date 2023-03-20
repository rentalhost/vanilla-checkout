<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Cielo\Tests;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Rentalhost\Vanilla\Cielo\Cielo;
use Rentalhost\Vanilla\Cielo\CieloProductLink;
use Rentalhost\Vanilla\Cielo\CieloTransactionType;

class CieloTest
    extends TestCase
{
    /**
     * @depends testMockHandler
     * @depends testGetAuthorization
     */
    public function testCreateLink(MockHandler $mockHandler, Cielo $cielo)
    {
        $mockHandler->append(
            new Response(201, [], json_encode([
                'id'       => '529aca91-2961-4976-8f7d-9e3f2fa8a0c9',
                'shortUrl' => 'http://bit.ly/2smqdhD',
            ]))
        );

        $productLink = new CieloProductLink('example', 12.34, CieloTransactionType::DIGITAL);
        $createdLink = $cielo->createLink($productLink);

        $this->assertSame('529aca91-2961-4976-8f7d-9e3f2fa8a0c9', $createdLink->id);
        $this->assertSame('http://bit.ly/2smqdhD', $createdLink->shortUrl);
    }

    /**
     * @depends testMockHandler
     */
    public function testGetAuthorization(MockHandler $mockHandler)
    {
        $cielo = new Cielo([
            'merchantId'  => $_ENV['CIELO_MERCHANT_ID'],
            'merchantKey' => $_ENV['CIELO_MERCHANT_KEY'],
            'handler'     => $mockHandler,
        ]);

        $mockHandler->append(
            new Response(200, [], json_encode([
                'access_token' => 'example',
            ]))
        );

        $this->assertSame('Bearer example', $cielo->getAuthorization());

        return $cielo;
    }

    public function testMockHandler()
    {
        $this->assertSame(true, true);

        return new MockHandler();
    }
}
