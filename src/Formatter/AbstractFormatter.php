<?php

namespace Namshi\Cuzzle\Formatter;

use GuzzleHttp\Cookie\CookieJarInterface;
use GuzzleHttp\Cookie\SetCookie;
use Psr\Http\Message\RequestInterface;

/**
 * Class AbstractFormatter it extracts information of a Guzzle request to array
 *
 * @package Namshi\Cuzzle\Formatter
 */
abstract class AbstractFormatter
{
    /**
     * @var string[]
     */
    protected $options = [];

    /**
     * @param RequestInterface $request
     * @param array            $options
     */
    protected function extractArguments (RequestInterface $request, array $options)
    {
        $this->extractHttpMethodArgument($request);
        $this->extractBodyArgument($request);
        $this->extractCookiesArgument($request, $options);
        $this->extractHeadersArgument($request);
        $this->extractUrlArgument($request);
    }

    /**
     * @param RequestInterface $request
     */
    private function extractHttpMethodArgument (RequestInterface $request)
    {
        $this->options['method'] = $request->getMethod();
    }

    /**
     * @param RequestInterface $request
     */
    private function extractBodyArgument (RequestInterface $request)
    {
        $body = $request->getBody();

        if ($body->isSeekable()) {
            $previousPosition = $body->tell();
            $body->rewind();
        }

        $contents = $body->getContents();

        if ($body->isSeekable()) {
            $body->seek($previousPosition);
        }

        if ($contents) {
            // clean input of null bytes
            $contents = str_replace(chr(0), '', $contents);
            $this->options['data'] = $contents;
        }

        //if get request has data Add G otherwise curl will make a post request
        if (!empty($this->options['data']) && ('GET' === $request->getMethod())) {
            $this->options['method'] = 'GET';
        }
    }

    /**
     * @param RequestInterface $request
     * @param array            $options
     */
    private function extractCookiesArgument (RequestInterface $request, array $options)
    {
        if (!isset($options['cookies']) || !$options['cookies'] instanceof CookieJarInterface) {
            return;
        }

        $values = [];
        $scheme = $request->getUri()->getScheme();
        $host = $request->getUri()->getHost();
        $path = $request->getUri()->getPath();

        /** @var SetCookie $cookie */
        foreach ($options['cookies'] as $cookie) {
            if ($cookie->matchesPath($path) && $cookie->matchesDomain($host) &&
                !$cookie->isExpired() && (!$cookie->getSecure() || $scheme == 'https')) {

                $values[] = $cookie->getName() . '=' . $cookie->getValue();
            }
        }

        if ($values) {
            $this->options['cookies'] = $values;
        }
    }

    /**
     * @param RequestInterface $request
     */
    private function extractHeadersArgument (RequestInterface $request)
    {
        foreach ($request->getHeaders() as $name => $header) {
            if ('host' === strtolower($name) && $header[0] === $request->getUri()->getHost()) {
                continue;
            }

            if ('user-agent' === strtolower($name)) {
                $this->options['user-agent'] = $header[0];
                continue;
            }

            foreach ((array) $header as $headerValue) {
                $this->options['headers'][$name] = $headerValue;
            }
        }
    }

    /**
     * @param RequestInterface $request
     */
    private function extractUrlArgument (RequestInterface $request)
    {
        $this->options['url'] = (string) $request->getUri()->withFragment('');
    }

    /**
     * @param RequestInterface $request
     * @param array            $options
     *
     * @return string | array
     */
    abstract public function format (RequestInterface $request, array $options = []);
}