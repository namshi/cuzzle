<?php

use Namshi\Cuzzle\Formatter\CurlFormatter;
use GuzzleHttp\Message\Request;
use GuzzleHttp\Post\PostBody;
use GuzzleHttp\Stream\Stream;

class CurlFormatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CurlFormatter
     */
    protected $curlFormatter;

    public function setUp()
    {
        $this->curlFormatter = new CurlFormatter();
    }

    public function testMultiLineDisabled()
    {
        $this->curlFormatter->setCommandLineLength(10);

        $request = new Request('GET', 'example.local', ['foo' => 'bar']);
        $curl    = $this->curlFormatter->format($request);

        $this->assertEquals(substr_count($curl, "\n"), 2);
    }

    public function testSkipHostInHeaders()
    {
        $request = new Request('GET', 'example.local');
        $curl    = $this->curlFormatter->format($request);

        $this->assertEquals("curl 'http://example.local'", $curl);
    }

    public function testSimpleGET()
    {
        $request = new Request('GET', 'example.local');
        $curl    = $this->curlFormatter->format($request);

        $this->assertEquals("curl 'http://example.local'", $curl);
    }

    public function testSimpleGETWithHeader()
    {
        $request = new Request('GET', 'example.local', ['foo' => 'bar']);
        $curl    = $this->curlFormatter->format($request);

        $this->assertEquals("curl 'http://example.local' -H 'foo: bar'", $curl);
    }

    public function testSimpleGETWithMultipleHeader()
    {
        $request = new Request('GET', 'example.local', ['foo' => 'bar', 'Accept-Encoding' => 'gzip,deflate,sdch']);
        $curl    = $this->curlFormatter->format($request);

        $this->assertEquals("curl 'http://example.local' -H 'foo: bar' -H 'Accept-Encoding: gzip,deflate,sdch'", $curl);
    }

    public function testGETWithQueryString()
    {
        $request = new Request('GET', 'example.local?foo=bar');
        $curl    = $this->curlFormatter->format($request);

        $this->assertEquals("curl 'http://example.local?foo=bar'", $curl);

        $request = new Request('GET', 'example.local');
        $request->getQuery()->set('foo', 'bar');
        $curl = $this->curlFormatter->format($request);

        $this->assertEquals("curl 'http://example.local?foo=bar'", $curl);
    }

    public function testPOST()
    {
        $body = new PostBody();
        $body->setField('foo', 'bar');
        $body->setField('hello', 'world');

        $request = new Request('POST', 'example.local', [], $body);
        $curl    = $this->curlFormatter->format($request);

        $this->assertContains("-d 'foo=bar&hello=world'", $curl);
    }

    public function testHEAD()
    {
        $request = new Request('HEAD', 'example.local');
        $curl    = $this->curlFormatter->format($request);

        $this->assertContains("--head", $curl);
    }

    public function testOPTIONS()
    {
        $request = new Request('OPTIONS', 'example.local');
        $curl    = $this->curlFormatter->format($request);

        $this->assertContains("-X OPTIONS", $curl);
    }

    public function testDELETE()
    {
        $request = new Request('DELETE', 'example.local/users/4');
        $curl    = $this->curlFormatter->format($request);

        $this->assertContains("-X DELETE", $curl);
    }

    public function testPUT()
    {
        $request = new Request('PUT', 'example.local', [], Stream::factory('foo=bar&hello=world'));
        $curl    = $this->curlFormatter->format($request);

        $this->assertContains("-d 'foo=bar&hello=world'", $curl);
        $this->assertContains("-X PUT", $curl);
    }

    public function testProperBodyReading()
    {
        $request = new Request('PUT', 'example.local', [], Stream::factory('foo=bar&hello=world'));
        $request->getBody()->getContents();

        $curl    = $this->curlFormatter->format($request);

        $this->assertContains("-d 'foo=bar&hello=world'", $curl);
        $this->assertContains("-X PUT", $curl);
    }
}
