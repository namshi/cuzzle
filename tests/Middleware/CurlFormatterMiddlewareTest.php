<?php

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;
use Namshi\Cuzzle\Middleware\CurlFormatterMiddleware;

class CurlFormatterMiddlewareTest extends \PHPUnit_Framework_TestCase
{
    public function testGet()
    {
        $mock = new MockHandler([new Response(204)]);
        $handler = HandlerStack::create($mock);
        $logger = $this->getMock(\Psr\Log\LoggerInterface::class);

        $logger
            ->expects($this->once())
            ->method('debug')
            ->with($this->stringStartsWith('curl'))
        ;

        $handler->after('cookies', new CurlFormatterMiddleware($logger));
        $client = new Client(['handler' => $handler]);

        $client->get('http://google.com');
    }
}
