<?php

namespace Namshi\Cuzzle\Formatter;

use Psr\Http\Message\RequestInterface;

/**
 * Class CurlArrayFormatter it formats a Guzzle request array
 * @package Namshi\Cuzzle\Formatter
 */
class CurlArrayFormatter extends AbstractFormatter
{
    /**
     * @param RequestInterface $request
     * @param array            $options
     *
     * @return array
     */
    public function format (RequestInterface $request, array $options = [])
    {
        $this->options = [];

        $this->extractArguments($request, $options);

        return $this->options;
    }
}
