<?php

namespace Namshi\Cuzzle\Middleware;

use Namshi\Cuzzle\Formatter\CurlArrayFormatter;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;

/**
 * Class CurlArrayFormatterMiddleware middleware
 * it allow to attach the CurlArrayFormatter to a Guzzle Request
 *
 * @package Namshi\Cuzzle\Middleware
 */
class CurlArrayFormatterMiddleware
{
    protected $logger;

    public function __construct (LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke (callable $handler)
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            $curlCommand = (new CurlArrayFormatter())->format($request, $options);
            $this->logger->debug(json_encode($curlCommand));

            return $handler($request, $options);
        };
    }
}
