<?php

namespace Emailcenter\Maxautomation;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

class MxaApiTest extends \PHPUnit_Framework_TestCase
{
    private $token = '1235test';
    private $clientId = 123;
    private $email = 'test@example.com';

    /**
     * @var MockHandler
     */
    private $mockHandler;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var MxaApi
     */
    private $mxaApi;

    /**
     * @var array
     */
    private $clientHistory = [];

    public function setUp()
    {
        $this->mockHandler = new MockHandler();
        $stack = HandlerStack::create($this->mockHandler);

        $stack->push(Middleware::history($this->clientHistory));
        $this->client = new Client(['handler' => $stack]);

        $this->mxaApi = new MxaApi($this->token);
        $this->mxaApi->setClient($this->client);
    }

    public function testSendContactRequest()
    {
        $this->mockHandler->append(
            new Response(200, [], json_encode(['name' => 'Trigger Name']))
        );
        $this->mxaApi->sendContact($this->clientId, $this->email);

        $this->assertCount(1, $this->clientHistory);

        /** @var Request $request */
        $request = $this->clientHistory[0]['request'];

        // Method and URI
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('trigger/Magento%20New%20Subscriber', $request->getUri());

        // Headers
        $this->assertTrue($request->hasHeader('X-Auth-Token'));
        $this->assertCount(1, $request->getHeader('X-Auth-Token'));
        $this->assertEquals($this->token, $request->getHeader('X-Auth-Token')[0]);

        $this->assertTrue($request->hasHeader('Content-Type'));
        $this->assertCount(1, $request->getHeader('Content-Type'));
        $this->assertEquals('application/json', $request->getHeader('Content-Type')[0]);

        $this->assertTrue($request->hasHeader('Accept'));
        $this->assertCount(1, $request->getHeader('Accept'));
        $this->assertEquals('application/json', $request->getHeader('Accept')[0]);

        // Body
        $expected = [
            '$contactId' => $this->clientId,
            '$email' => $this->email
        ];
        $this->assertEquals(json_encode($expected), (string)$request->getBody());
    }

    /**
     * Check that an error response does NOT throw any exceptions
     */
    public function testSendContactSwallowsRequestExceptions()
    {
        $this->mockHandler->append(
            new Response(401, [], 'Unauthorized')
        );
        $this->mxaApi->sendContact($this->clientId, $this->email);

        $this->assertCount(1, $this->clientHistory);
    }
}
