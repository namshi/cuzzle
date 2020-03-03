<?php

use GuzzleHttp\Psr7\Request;
use function GuzzleHttp\Psr7\stream_for;
use Namshi\Cuzzle\Formatter\CurlArrayFormatter;
use Namshi\Cuzzle\Formatter\CurlFormatter;

class CurlArrayFormatterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CurlFormatter
     */
    protected $curlFormatter;

    public function setUp ()
    {
        $this->curlFormatter = new CurlArrayFormatter();
    }

    public function testUserAgent ()
    {
        $request = new Request('GET', 'http://example.local', [
            'user-agent' => 'facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)',
        ]);
        $curl = $this->curlFormatter->format($request);

        $this->assertArrayHasKey("user-agent", $curl);
        $this->assertEquals("facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)", $curl['user-agent']);
    }

    public function testSimpleGET ()
    {
        $request = new Request('GET', 'http://example.local');
        $curl = $this->curlFormatter->format($request);

        $this->assertArrayHasKey("method", $curl);
    }

    public function testSimpleGETWithHeader ()
    {
        $request = new Request('GET', 'http://example.local', [ 'foo' => 'bar' ]);
        $curl = $this->curlFormatter->format($request);

        $this->assertArrayHasKey("headers", $curl);
        $this->assertArrayHasKey("foo", $curl['headers']);
        $this->assertEquals("bar", $curl['headers']['foo']);
    }

    public function testSimpleGETWithMultipleHeader ()
    {
        $request = new Request('GET', 'http://example.local', [
            'foo'             => 'bar',
            'Accept-Encoding' => 'gzip,deflate,sdch',
        ]);
        $curl = $this->curlFormatter->format($request);

        $this->assertArrayHasKey("headers", $curl);
        $this->assertEquals("bar", $curl['headers']['foo']);
        $this->assertEquals("gzip,deflate,sdch", $curl['headers']['Accept-Encoding']);
    }

    public function testGETWithQueryString ()
    {
        $request = new Request('GET', 'http://example.local?foo=bar');
        $curl = $this->curlFormatter->format($request);

        $this->assertArrayHasKey('url', $curl);
        $this->stringContains('foo=bar', $curl['url']);

        $body = stream_for(http_build_query([ 'foo' => 'bar', 'hello' => 'world' ], '', '&'));
        $request = new Request('GET', 'http://example.local', [], $body);
        $curl = $this->curlFormatter->format($request);

        $this->assertEquals('GET', $curl['method']);
        $this->assertArrayHasKey('data', $curl);
        $this->assertEquals('foo=bar&hello=world', $curl['data']);
    }

    public function testPOST ()
    {
        $body = stream_for(http_build_query([ 'foo' => 'bar', 'hello' => 'world' ], '', '&'));

        $request = new Request('POST', 'http://example.local', [], $body);
        $curl = $this->curlFormatter->format($request);

        $this->assertEquals('POST', $curl['method']);
        $this->assertNotEquals('GET', $curl['method']);
        $this->assertArrayHasKey('data', $curl);
        $this->assertEquals('foo=bar&hello=world', $curl['data']);
    }

    public function testHEAD ()
    {
        $request = new Request('HEAD', 'http://example.local');
        $curl = $this->curlFormatter->format($request);

        $this->assertEquals('HEAD', $curl['method']);
    }

    public function testOPTIONS ()
    {
        $request = new Request('OPTIONS', 'http://example.local');
        $curl = $this->curlFormatter->format($request);

        $this->assertEquals('OPTIONS', $curl['method']);
    }

    public function testDELETE ()
    {
        $request = new Request('DELETE', 'http://example.local/users/4');
        $curl = $this->curlFormatter->format($request);

        $this->assertEquals('DELETE', $curl['method']);
    }

    public function testPUT ()
    {
        $request = new Request('PUT', 'http://example.local', [], stream_for('foo=bar&hello=world'));
        $curl = $this->curlFormatter->format($request);

        $this->assertEquals('PUT', $curl['method']);
        $this->assertArrayHasKey('data', $curl);
        $this->assertEquals('foo=bar&hello=world', $curl['data']);
    }

    public function testProperBodyReading ()
    {
        $request = new Request('PUT', 'http://example.local', [], stream_for('foo=bar&hello=world'));
        $content = $request->getBody()->getContents();

        $curl = $this->curlFormatter->format($request);

        $this->assertEquals($content, $curl['data']);
        $this->assertEquals('foo=bar&hello=world', $curl['data']);
        $this->assertEquals("PUT", $curl['method']);
    }

    /**
     * @dataProvider getHeadersAndBodyData
     *
     * @param $headers
     * @param $body
     */
    public function testExtractBodyArgument ($headers, $body)
    {
        // clean input of null bytes
        $body = str_replace(chr(0), '', $body);
        $request = new Request('POST', 'http://example.local', $headers, stream_for($body));
        $curl = $this->curlFormatter->format($request);

        $this->assertEquals('foo=bar&hello=world', $curl['data']);
    }

    /**
     * The data provider for testExtractBodyArgument
     *
     * @return array
     */
    public function getHeadersAndBodyData ()
    {
        return [
            [
                [ 'X-Foo' => 'Bar' ],
                chr(0) . 'foo=bar&hello=world',
            ],
        ];
    }
}
