<?php

namespace Namshi\Cuzzle\Middleware;

use Namshi\Cuzzle\Formatter\CurlFormatter;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Class CurlFormatterMiddleware middleware
 * it allow to attach the CurlFormatter to a Guzzle Request
 *
 * @package Namshi\Cuzzle\Middleware
 */
class CurlFormatterMiddleware
{
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            $curlCommand = (new CurlFormatter())->format($request, $options);
            $this->logger->debug($curlCommand);

            return $handler($request, $options);
        };
    }
}
