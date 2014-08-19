<?php

use Namshi\Guzzle\Formatter\CurlShellFormatter;
use GuzzleHttp\Message\Request;

class CurlShellFormatterTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var CurlShellFormatter
     */
    protected $curlFormatter;

    public function setUp()
    {
        $this->curlFormatter = new CurlShellFormatter();
    }

    public function testSimpleGET()
    {
        $r = new Request('GET', 'example.local');

        $this->assertEquals("curl 'http://example.local'", $this->curlFormatter->format($r));
    }

    public function testSimpleGETWithHeader()
    {
        $r = new Request('GET', 'example.local', ['foo' => 'bar']);

        $this->assertEquals("curl 'http://example.local' -H='foo: bar'", $this->curlFormatter->format($r));
    }

    public function testSimpleGETWithMultipleHeader()
    {
        $r = new Request('GET', 'example.local', ['foo' => 'bar', 'Accept-Encoding' => 'gzip,deflate,sdch']);

        $this->assertEquals("curl 'http://example.local' -H='foo: bar' -H='Accept-Encoding: gzip,deflate,sdch'", $this->curlFormatter->format($r));
    }

    public function testGETWithQueryString()
    {
        $r = new Request('GET', 'example.local?foo=bar');

        $this->assertEquals("curl 'http://example.local?foo=bar'", $this->curlFormatter->format($r));
    }
}
