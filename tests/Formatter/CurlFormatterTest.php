<?php

use Namshi\Cuzzle\Formatter\CurlFormatter;
use GuzzleHttp\Psr7\Request;

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

        $request = new Request('GET', 'http://example.local', ['foo' => 'bar']);
        $curl    = $this->curlFormatter->format($request);

        $this->assertEquals(substr_count($curl, "\n"), 2);
    }

    public function testSkipHostInHeaders()
    {
        $request = new Request('GET', 'http://example.local');
        $curl    = $this->curlFormatter->format($request);

        $this->assertEquals("curl 'http://example.local'", $curl);
    }

    public function testSimpleGET()
    {
        $request = new Request('GET', 'http://example.local');
        $curl    = $this->curlFormatter->format($request);

        $this->assertEquals("curl 'http://example.local'", $curl);
    }

    public function testSimpleGETWithHeader()
    {
        $request = new Request('GET', 'http://example.local', ['foo' => 'bar']);
        $curl    = $this->curlFormatter->format($request);

        $this->assertEquals("curl 'http://example.local' -H 'foo: bar'", $curl);
    }

    public function testSimpleGETWithMultipleHeader()
    {
        $request = new Request('GET', 'http://example.local', ['foo' => 'bar', 'Accept-Encoding' => 'gzip,deflate,sdch']);
        $curl    = $this->curlFormatter->format($request);

        $this->assertEquals("curl 'http://example.local' -H 'foo: bar' -H 'Accept-Encoding: gzip,deflate,sdch'", $curl);
    }

    public function testGETWithQueryString()
    {
        $request = new Request('GET', 'http://example.local?foo=bar');
        $curl    = $this->curlFormatter->format($request);

        $this->assertEquals("curl 'http://example.local?foo=bar'", $curl);

        $request = new Request('GET', 'http://example.local?foo=bar');
        $curl = $this->curlFormatter->format($request);

        $this->assertEquals("curl 'http://example.local?foo=bar'", $curl);

        $body = \GuzzleHttp\Psr7\stream_for(http_build_query(['foo' => 'bar', 'hello' => 'world'], '', '&'));

        $request = new Request('GET', 'http://example.local',[],$body);
        $curl    = $this->curlFormatter->format($request);

        $this->assertEquals("curl 'http://example.local' -G  -d 'foo=bar&hello=world'",$curl);

    }

    public function testPOST()
    {
        $body = \GuzzleHttp\Psr7\stream_for(http_build_query(['foo' => 'bar', 'hello' => 'world'], '', '&'));

        $request = new Request('POST', 'http://example.local', [], $body);
        $curl    = $this->curlFormatter->format($request);

        $this->assertContains("-d 'foo=bar&hello=world'", $curl);
        $this->assertNotContains(" -G ", $curl);
    }

    public function testHEAD()
    {
        $request = new Request('HEAD', 'http://example.local');
        $curl    = $this->curlFormatter->format($request);

        $this->assertContains("--head", $curl);
    }

    public function testOPTIONS()
    {
        $request = new Request('OPTIONS', 'http://example.local');
        $curl    = $this->curlFormatter->format($request);

        $this->assertContains("-X OPTIONS", $curl);
    }

    public function testDELETE()
    {
        $request = new Request('DELETE', 'http://example.local/users/4');
        $curl    = $this->curlFormatter->format($request);

        $this->assertContains("-X DELETE", $curl);
    }

    public function testPUT()
    {
        $request = new Request('PUT', 'http://example.local', [], \GuzzleHttp\Psr7\stream_for('foo=bar&hello=world'));
        $curl    = $this->curlFormatter->format($request);

        $this->assertContains("-d 'foo=bar&hello=world'", $curl);
        $this->assertContains("-X PUT", $curl);
    }

    public function testProperBodyReading()
    {
        $request = new Request('PUT', 'http://example.local', [], \GuzzleHttp\Psr7\stream_for('foo=bar&hello=world'));
        $request->getBody()->getContents();

        $curl    = $this->curlFormatter->format($request);

        $this->assertContains("-d 'foo=bar&hello=world'", $curl);
        $this->assertContains("-X PUT", $curl);
    }

    /**
     * @dataProvider getHeadersAndBodyData
     */
    public function testExtractBodyArgument($headers, $body)
    {
        // clean input of null bytes
        $body = str_replace(chr(0), '', $body);
        $request = new Request('POST', 'http://example.local', $headers, \GuzzleHttp\Psr7\stream_for($body));

        $curl = $this->curlFormatter->format($request);

        $this->assertContains('foo=bar&hello=world', $curl);
    }

    /**
     * The data provider for testExtractBodyArgument
     *
     * @return array
     */
    public function getHeadersAndBodyData()
    {
        return [
            [
                ['X-Foo' => 'Bar'],
                chr(0). 'foo=bar&hello=world',
            ],
        ];
    }
}
