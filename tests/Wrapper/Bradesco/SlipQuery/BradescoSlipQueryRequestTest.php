<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Checkout\Tests\Wrapper\Bradesco\SlipQuery;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Rentalhost\Vanilla\Checkout\Tests\Traits\MockHandlerTrait;
use Rentalhost\Vanilla\Checkout\Wrapper\Bradesco\SlipQuery\BradescoSlipQuery;
use Rentalhost\Vanilla\Checkout\Wrapper\Bradesco\SlipQuery\BradescoSlipQueryResponseStatus;

class BradescoSlipQueryRequestTest
    extends TestCase
{
    use MockHandlerTrait;

    /** @depends testMockHandler */
    public function testResponse(MockHandler $mockHandler)
    {
        $mockHandler->append(
        // Authorization token.
            new Response(200, [], json_encode([
                'token' => [
                    'token' => 'mock',
                ],
            ])),
            // Request #1:
            new Response(200, [], json_encode([
                'paging'  => [ 'nextOffset' => 2 ],
                'pedidos' => [
                    [
                        'numero'        => '1',
                        'valor'         => '1500',
                        'valorPago'     => '1500',
                        'data'          => '10/04/2023 17:33:22',
                        'dataPagamento' => '10/04/2023 17:33:22',
                        'status'        => '21',
                        'erro'          => '0',
                    ],
                ],
            ])),
            // Request #2:
            new Response(200, [], json_encode([
                'paging'  => [ 'nextOffset' => -1 ],
                'pedidos' => [
                    [
                        'numero'        => '2',
                        'valor'         => '2551',
                        'valorPago'     => '2500',
                        'data'          => '10/04/2023 17:40:00',
                        'dataPagamento' => '10/04/2023 17:40:00',
                        'status'        => '22',
                        'erro'          => '0',
                    ],
                ],
            ])),
        );

        $request   = new BradescoSlipQuery([
            'merchantId'       => 'mock',
            'merchantKey'      => 'mock',
            'merchantUsername' => 'mock',
            'handler'          => $mockHandler,
        ]);
        $responses = $request->query();

        $this->assertSame(2, count($responses));

        $response1 = $responses[0];

        $this->assertSame('1', $response1->reference);
        $this->assertSame(15.0, $response1->value);
        $this->assertSame(15.0, $response1->valuePaid);
        $this->assertSame(true, $response1->isPaidEqual());
        $this->assertSame('2023-04-10 17:33:22', $response1->date->format('Y-m-d H:i:s'));
        $this->assertSame('2023-04-10 17:33:22', $response1->datePaid->format('Y-m-d H:i:s'));
        $this->assertSame(BradescoSlipQueryResponseStatus::PAID_EQUAL, $response1->status);
        $this->assertSame(0, $response1->error);

        $response2 = $responses[1];

        $this->assertSame('2', $response2->reference);
        $this->assertSame(25.51, $response2->value);
        $this->assertSame(25.0, $response2->valuePaid);
        $this->assertSame(false, $response2->isPaidEqual());
        $this->assertSame('2023-04-10 17:40:00', $response2->date->format('Y-m-d H:i:s'));
        $this->assertSame('2023-04-10 17:40:00', $response2->datePaid->format('Y-m-d H:i:s'));
        $this->assertSame(BradescoSlipQueryResponseStatus::PAID_LOWER, $response2->status);
        $this->assertSame(0, $response2->error);
    }
}
