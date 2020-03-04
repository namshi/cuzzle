<?php

namespace Namshi\Cuzzle\Formatter;

use GuzzleHttp\Cookie\CookieJarInterface;
use GuzzleHttp\Cookie\SetCookie;
use Psr\Http\Message\RequestInterface;

/**
 * Class CurlFormatter it formats a Guzzle request to a cURL shell command
 *
 * @package Namshi\Cuzzle\Formatter
 */
class CurlFormatter extends AbstractFormatter
{
    /**
     * @var string
     */
    protected $command;

    /**
     * @var int
     */
    protected $currentLineLength;

    /**
     * @var string[]
     */
    protected $format;

    /**
     * @var int
     */
    protected $commandLineLength;

    /**
     * @param int $commandLineLength
     */
    function __construct($commandLineLength =  100)
    {
        $this->commandLineLength = $commandLineLength;
    }

    /**
     * @param RequestInterface $request
     * @param array            $options
     * @return string
     */
    public function format(RequestInterface $request, array $options = [])
    {
        $this->command           = 'curl';
        $this->currentLineLength = strlen($this->command);
        $this->format           = [];

        $this->extractArguments($request, $options);
        $this->serializeOptions();
        $this->addOptionsToCommand();

        return $this->command;
    }

    /**
     * @param int $commandLineLength
     */
    public function setCommandLineLength($commandLineLength)
    {
        $this->commandLineLength = $commandLineLength;
    }

    /**
     * @param $name
     * @param null $value
     */
    protected function addOption($name, $value = null)
    {
        if (isset($this->format[$name])) {
            if (!is_array($this->format[$name])) {
                $this->format[$name] = (array) $this->format[$name];
            }

            $this->format[$name][] = $value;
        } else {
            $this->format[$name] = $value;
        }

    }

    /**
     * @param $part
     */
    protected function addCommandPart($part)
    {
        $this->command .= ' ';

        if ($this->commandLineLength > 0 && $this->currentLineLength + strlen($part) > $this->commandLineLength) {
            $this->currentLineLength = 0;
            $this->command .= "\\\n  ";
        }

        $this->command .= $part;
        $this->currentLineLength += strlen($part) + 2;
    }

    protected function addOptionsToCommand()
    {
        ksort($this->format);

        if ($this->format) {
            foreach ($this->format as $name => $value) {
                if (is_array($value)) {
                    foreach ($value as $subValue) {
                        $this->addCommandPart("-{$name} {$subValue}");
                    }
                } else {
                    $this->addCommandPart("-{$name} {$value}");
                }
            }
        }
    }

    private function serializeOptions()
    {
        $this->serializeHttpMethodOption();
        $this->serializeBodyOption();
        $this->serializeCookiesOption();
        $this->serializeHeadersOption();
        $this->serializeUrlOption();
    }

    private function serializeHttpMethodOption()
    {
        if ('GET' !== $this->options['method']) {
            if ('HEAD' === $this->options['method']) {
                $this->addOption('-head');
            } else {
                $this->addOption('X', $this->options['method']);
            }
        }
    }

    private function serializeBodyOption()
    {
        if (isset($this->options['data'])) {
            $this->addOption('d', escapeshellarg($this->options['data']));
            if ('GET' == $this->options['method']) {
                $this->addOption('G');
            }
        }
    }

    private function serializeCookiesOption()
    {
        if (isset($this->options['cookies'])) {
            $this->addOption('b', escapeshellarg(implode('; ', $this->options['cookies'])));
        }
    }

    private function serializeHeadersOption()
    {
        if (isset($this->options['user-agent'])) {
            $this->addOption('A', escapeshellarg($this->options['user-agent']));
        }

        if (isset($this->options['headers'])) {
            foreach ($this->options['headers'] as $name => $value) {
                $this->addOption('H', escapeshellarg("{$name}: {$value}"));
            }
        }
    }

    private function serializeUrlOption()
    {
        $this->addCommandPart(escapeshellarg((string) $this->options['url']));
    }
}
