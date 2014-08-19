<?php

use  Monolog\Logger;
use  Monolog\Handler\TestHandler;
use  GuzzleHttp\Client;
use  Namshi\Cuzzle\Subscriber\CurlFormatterSubscriber;

class CurlFormatterSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var TestHandler
     */
    protected $loggerHandler;


    public function setUp()
    {
        $logger                  = new Logger('guzzle.curl');
        $this->loggerHandler     = new TestHandler();
        $this->client            = new Client();
        $curlFormatterSubscriber = new CurlFormatterSubscriber($logger);

        $logger->pushHandler($this->loggerHandler);
        $this->client->getEmitter()->attach($curlFormatterSubscriber);
    }

    public function testGet()
    {
        $this->client->get('http://google.com');

        $loggedCurl = $this->loggerHandler->getRecords()[0]['message'];
        $this->assertContains('curl', $loggedCurl);
    }

}