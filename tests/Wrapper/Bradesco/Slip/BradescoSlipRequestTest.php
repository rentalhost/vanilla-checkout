<?php

declare(strict_types = 1);

namespace Rentalhost\Vanilla\Checkout\Tests\Wrapper\Bradesco\Slip;

use DateTime;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Rentalhost\Vanilla\Checkout\Tests\Traits\MockHandlerTrait;
use Rentalhost\Vanilla\Checkout\Wrapper\Bradesco\Slip\Bradesco;
use Rentalhost\Vanilla\Checkout\Wrapper\Bradesco\Slip\BradescoSlipRequest;
use Rentalhost\Vanilla\Checkout\Wrapper\Bradesco\Slip\Data\DataAddress;
use Rentalhost\Vanilla\Checkout\Wrapper\Bradesco\Slip\Data\DataBuyer;
use Rentalhost\Vanilla\Checkout\Wrapper\Bradesco\Slip\Data\DataShop;
use Rentalhost\Vanilla\Checkout\Wrapper\Bradesco\Slip\Exceptions\BradescoSlipResponseException;

class BradescoSlipRequestTest
    extends TestCase
{
    use MockHandlerTrait;

    private static function getRequestModel(): BradescoSlipRequest
    {
        return new BradescoSlipRequest(
            'P123456789',
            1000,
            15.0,
            'description',
            "Line 1\nLine 2\nLong Line Long Line Long Line Long Line Long Line Long Line Long Line Long Line Long Line Long Line Long Line Long Line Long Line Long\nLine 6",
            new DataShop('Shop', 'description', '33'),
            new DataBuyer('Buyer', '12345678910'),
            new DataAddress('12345678', 'Street', 'Number', 'Complement', 'District', 'City', 'RJ'),
            new DateTime('now')
        );
    }

    public function testExceptionInvalidRequestReference()
    {
        $this->expectExceptionMessage('invalid request reference');

        new BradescoSlipRequest(
            'X',
            1000,
            15.0,
            'description',
            '',
            new DataShop('Shop', 'description', '33'),
            new DataBuyer('Buyer', '12345678910'),
            new DataAddress('12345678', 'Street', 'Number', 'Complement', 'District', 'City', 'RJ'),
            new DateTime('now')
        );
    }

    public function testExceptionInvalidRequestReferenceLong()
    {
        $this->expectExceptionMessage('invalid request reference');

        new BradescoSlipRequest(
            'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789',
            1000,
            15.0,
            'description',
            '',
            new DataShop('Shop', 'description', '33'),
            new DataBuyer('Buyer', '12345678910'),
            new DataAddress('12345678', 'Street', 'Number', 'Complement', 'District', 'City', 'RJ'),
            new DateTime('now')
        );
    }

    public function testExceptionRequestNumberGreater()
    {
        $this->expectExceptionMessage('request number must be lower or equal to 99999999999');

        new BradescoSlipRequest(
            'P123456789',
            100000000000,
            15.0,
            'description',
            '',
            new DataShop('Shop', 'description', '33'),
            new DataBuyer('Buyer', '12345678910'),
            new DataAddress('12345678', 'Street', 'Number', 'Complement', 'District', 'City', 'RJ'),
            new DateTime('now')
        );
    }

    public function testExceptionRequestNumberLower()
    {
        $this->expectExceptionMessage('request number must be greater or equal to 1000');

        new BradescoSlipRequest(
            'P123456789',
            999,
            15.0,
            'description',
            "Line 1\nLine 2\nLong Line Long Line Long Line Long Line Long Line Long Line Long Line Long Line Long Line Long Line Long Line Long Line Long Line Long\nLine 6",
            new DataShop('Shop', 'description', '33'),
            new DataBuyer('Buyer', '12345678910'),
            new DataAddress('12345678', 'Street', 'Number', 'Complement', 'District', 'City', 'RJ'),
            new DateTime('now')
        );
    }

    /** @depends testMockHandler */
    public function testResponse(MockHandler $mockHandler)
    {
        $mockHandler->append(
            new Response(200, [], json_encode([
                'boleto' => [
                    'url_acesso' => 'https://...',
                ],
                'status' => [
                    'codigo'   => 0,
                    'mensagem' => 'OPERACAO REALIZADA COM SUCESSO',
                ],
            ]))
        );

        $bradesco = new Bradesco([
            'merchantId'  => 'mock',
            'merchantKey' => 'mock',
            'handler'     => $mockHandler,
        ]);

        $request  = self::getRequestModel();
        $response = $bradesco->createBillet($request);

        $this->assertSame(0, $response->code);
        $this->assertSame('OPERACAO REALIZADA COM SUCESSO', $response->message);
        $this->assertSame('https://...', $response->url);
    }

    /** @depends testMockHandler */
    public function testResponseException(MockHandler $mockHandler)
    {
        $this->expectException(BradescoSlipResponseException::class);
        $this->expectExceptionCode(999);
        $this->expectExceptionMessage('ERRO AO REALIZAR OPERACAO');

        $mockHandler->append(
            new Response(400, [], json_encode([
                'status' => [
                    'codigo'   => 999,
                    'mensagem' => 'ERRO AO REALIZAR OPERACAO',
                ],
            ]))
        );

        $bradesco = new Bradesco([
            'merchantId'  => 'mock',
            'merchantKey' => 'mock',
            'handler'     => $mockHandler,
        ]);

        $bradesco->createBillet(self::getRequestModel());
    }

    public function testValidRequest()
    {
        $request = self::getRequestModel();

        $this->assertSame('P123456789', $request->requestReference);
        $this->assertSame(1000, $request->requestNumber);
        $this->assertSame(15.0, $request->productPrice);
        $this->assertSame('description', $request->productDescription);
        $this->assertSame(
            "Line 1\nLine 2\nLong Line Long Line Long Line Long Line Long Line Long Line Long Line Long Line Long Line Long Line Long Line Long Line Long Line Long\nLine 6",
            $request->productInstructions
        );
        $this->assertInstanceOf(DataShop::class, $request->shop);
        $this->assertInstanceOf(DataBuyer::class, $request->buyer);
        $this->assertInstanceOf(DataAddress::class, $request->buyerAddress);
        $this->assertInstanceOf(DateTime::class, $request->dateExpiration);

        $this->assertSame([
            'Line 1',
            'Line 2',
            'Long Line Long Line Long Line Long Line Long Line Long Line ',
            'Long Line Long Line Long Line Long Line Long Line Long Line ',
            'Long Line Long',
            'Line 6',
        ], $request->getInstructions());

        $transactionArray = $request->toTransactionArray('123');
        $transactionToken = $transactionArray['token_request_confirmacao_pagamento'];

        $transactionArray['boleto']['data_emissao']    = '2023-04-10';
        $transactionArray['boleto']['data_vencimento'] = '2023-04-10';

        unset($transactionArray['token_request_confirmacao_pagamento']);

        $this->assertSame(64, strlen($transactionToken));
        $this->assertSame([
            'merchant_id'    => '123',
            'meio_pagamento' => 300,
            'pedido'         => [
                'numero'    => 'P123456789',
                'valor'     => 1500,
                'descricao' => 'description',
            ],
            'comprador'      => [
                'nome'      => 'Buyer',
                'documento' => '12345678910',
                'endereco'  => [
                    'cep'         => '12345678',
                    'logradouro'  => 'Street',
                    'numero'      => 'Number',
                    'complemento' => 'Complement',
                    'bairro'      => 'District',
                    'cidade'      => 'City',
                    'uf'          => 'RJ',
                ],
            ],
            'boleto'         => [
                'beneficiario'       => 'Shop',
                'carteira'           => '33',
                'nosso_numero'       => 1000,
                'data_emissao'       => '2023-04-10',
                'data_vencimento'    => '2023-04-10',
                'valor_titulo'       => 1500,
                'url_logotipo'       => null,
                'mensagem_cabecalho' => 'description',
                'tipo_renderizacao'  => 2,
                'instrucoes'         => [
                    'instrucao_linha_1' => 'Line 1',
                    'instrucao_linha_2' => 'Line 2',
                    'instrucao_linha_3' => 'Long Line Long Line Long Line Long Line Long Line Long Line ',
                    'instrucao_linha_4' => 'Long Line Long Line Long Line Long Line Long Line Long Line ',
                    'instrucao_linha_5' => 'Long Line Long',
                    'instrucao_linha_6' => 'Line 6',
                ],
            ],
        ], $transactionArray);

        $request = new BradescoSlipRequest(
            'P123456789',
            1000,
            15.0,
            'description',
            '',
            new DataShop('Shop', 'description', '33'),
            new DataBuyer('Buyer', '12345678910'),
            new DataAddress('12345678', 'Street', 'Number', 'Complement', 'District', 'City', 'RJ'),
            new DateTime('now')
        );

        $this->assertSame([], $request->getInstructions());

        $request = new BradescoSlipRequest(
            'P123456789',
            1000,
            15.0,
            'description',
            null,
            new DataShop('Shop', 'description', '33'),
            new DataBuyer('Buyer', '12345678910'),
            new DataAddress('12345678', 'Street', 'Number', 'Complement', 'District', 'City', 'RJ'),
            new DateTime('now')
        );

        $this->assertSame([], $request->getInstructions());

        $request = new BradescoSlipRequest(
            'P123456789',
            1000,
            15.0,
            'description',
            [ 'line 1', '', null, 'line 4', '', '', '', '', '', '', '', 'long long long long long long long long long long long long 123' ],
            new DataShop('Shop', 'description', '33'),
            new DataBuyer('Buyer', '12345678910'),
            new DataAddress('12345678', 'Street', 'Number', 'Complement', 'District', 'City', 'RJ'),
            new DateTime('now')
        );

        $this->assertSame([ 'line 1', '', '', 'line 4', '', '', '', '', '', '', '', 'long long long long long long long long long long long long ' ],
            $request->getInstructions());
    }
}
