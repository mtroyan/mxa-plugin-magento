<?php
declare(strict_types=1);

namespace Emailcenter\Maxautomation;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class MxaApiTest extends TestCase
{
    private $token = '1235test';
    private $clientId = 123;
    private $email = 'test@example.com';

    /**
     * @var MockHandler
     */
    private $mockHandler;

    /**
     * @var Request $request
     */
    private $request;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var MxaApi
     */
    private $mxaApi;

    public function setUp()
    {
        $this->mockHandler = new MockHandler([
            new Response(200),
            new Response(401)
            ]);
        $stack = HandlerStack::create($this->mockHandler);

        $history = [];
        $stack->push(Middleware::history($history));
        $this->client = new Client(['handler' => $stack]);

        $this->mxaApi = new MxaApi($this->token);
        $this->mxaApi->setClient($this->client);
        $this->mxaApi->sendContact($this->clientId, $this->email);

        $this->assertCount(1, $history);

        $this->request = $history[0]['request'];
    }

    public function testMethod()
    {
        $this->assertEquals('POST', $this->request->getMethod());
    }

    public function testUri()
    {
        $this->assertEquals('trigger/Magento%20New%20Subscriber', $this->request->getUri());
    }

    public function testHeaders()
    {
        $headers = [
            'X-Auth-Token'   => [$this->token],
            'Content-Type'   => ['application/json'],
            'Accept'         => ['application/json']
        ];

        $headerContentType = $this->request->getHeaders()['Content-Type'][0];
        $this->assertEquals($headers['Content-Type'][0], $headerContentType);

        $headerToken = $this->request->getHeaders()['X-Auth-Token'][0];
        $this->assertEquals($headers['X-Auth-Token'][0], $headerToken);

        $headerAccept = $this->request->getHeaders()['Accept'][0];
        $this->assertEquals($headers['Accept'][0], $headerAccept);
    }

    public function testJson()
    {
        $expected = [
            '$contactId' => $this->clientId,
            '$email' => $this->email
        ];
        $json = \GuzzleHttp\json_decode($this->request->getBody(), true);
        $this->assertEquals($expected, $json);
    }

    public function test401Exception()
    {
        $this->expectException(RequestException::class);
        $this->client->request('dummy 401 Unauthorised');
    }
}
