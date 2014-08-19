<?php

namespace Client;

use GuzzleHttp\Client;
use Namshi\Cuzzle\Formatter\CurlFormatter;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var CurlFormatter
     */
    protected $curlFormatter;

    public function setUp()
    {
        $this->client        = new Client();
        $this->curlFormatter = new CurlFormatter();
    }

    public function testGetWithCookies()
    {
        $request = $this->client->createRequest('GET', 'http://local.example', ['cookies' => ['Foo' => 'Bar', 'identity' => 'xyz']]);
        $curl    = $this->curlFormatter->format($request);

        $this->assertNotContains("-H 'Host: local.example'", $curl);
        $this->assertContains("-b 'Foo=Bar; identity=xyz'", $curl);
    }

    public function testPOST()
    {
        $request = $this->client->createRequest('POST', 'http://local.example', ['body' => ['foo' => 'bar', 'hello' => 'world']]);
        $curl    = $this->curlFormatter->format($request);

        $this->assertContains("-d 'foo=bar&hello=world'", $curl);
    }

    public function testPUT()
    {
        $request = $this->client->createRequest('PUT', 'http://local.example', ['body' => ['foo' => 'bar', 'hello' => 'world']]);
        $curl    = $this->curlFormatter->format($request);

        $this->assertContains("-d 'foo=bar&hello=world'", $curl);
        $this->assertContains('-X PUT', $curl);
    }

    public function testDELETE()
    {
        $request = $this->client->createRequest('DELETE', 'http://local.example');
        $curl    = $this->curlFormatter->format($request);

        $this->assertContains('-X DELETE', $curl);
    }

    public function testHEAD()
    {
        $request = $this->client->createRequest('HEAD', 'http://local.example');
        $curl    = $this->curlFormatter->format($request);

        $this->assertContains("curl 'http://local.example' --head", $curl);
    }

    public function testOPTIONS()
    {
        $request = $this->client->createRequest('OPTIONS', 'http://local.example');
        $curl    = $this->curlFormatter->format($request);

        $this->assertContains('-X OPTIONS', $curl);
    }

} 