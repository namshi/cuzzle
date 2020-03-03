<?php

namespace Namshi\Cuzzle\Middleware;

use Namshi\Cuzzle\Formatter\CurlFormatter;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;

/**
 * Class CurlFormatterMiddleware middleware
 * it allow to attach the CurlFormatter to a Guzzle Request
 *
 * @package Namshi\Cuzzle\Middleware
 */
class CurlFormatterMiddleware
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
            $curlCommand = (new CurlFormatter())->format($request, $options);
            $this->logger->{$this->level}($curlCommand);

            return $handler($request, $options);
        };
    }
}
