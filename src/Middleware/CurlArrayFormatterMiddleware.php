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
    protected $logger, $level;

    public function __construct (LoggerInterface $logger, $level = 'debug')
    {
        $this->logger = $logger;
        $this->level = $level;
    }

    public function __invoke (callable $handler)
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            $curlArray = (new CurlArrayFormatter())->format($request, $options);
            $this->logger->{$this->level}(json_encode($curlArray));

            return $handler($request, $options);
        };
    }
}
