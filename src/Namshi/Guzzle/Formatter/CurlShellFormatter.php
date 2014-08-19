<?php

namespace Namshi\Guzzle\Formatter;

use GuzzleHttp\Message\Request;
use GuzzleHttp\Url;

/**
 * Class CurlShellFormatter
 * @package Namshi\Guzzle\Formatter
 */
class CurlShellFormatter
{

    const LINE_LENGTH_LIMIT = 100;

    /**
     * @var string
     */
    protected $command;

    /**
     * @var int
     */
    protected $curLineLength;

    /**
     * @var string[]
     */
    protected $shortOptions;

    /**
     * @var string[]
     */
    protected $options;

    /**
     * @param Request $request
     * @return string
     */
    public function format(Request $request)
    {
        $this->command       = 'curl';
        $this->curLineLength = strlen($this->command);
        $this->shortOptions  = [];
        $this->options       = [];

        if ($request->getConfig()['connect_timeout']) {
            $this->addOption('-connect-timeout', $request->getConfig()['connect_timeout']);
        }

        if ($request->getConfig()['save_to']) {
            $this->addOption('o', $request->getConfig()['save_to']);
        }

        if ($request->getConfig()['allow_redirects']) {
            $this->addOption('L');
        }

        if ($request->getConfig()['timeout']) {
            $this->addOption('m', $request->getConfig()['timeout']);
        }

        foreach ($request->getHeaders() as $name => $header) {
            $this->addOption('H', "'" . addcslashes("{$name}: " . implode(', ', $header), "'") . "'");
        }

        ksort($this->options);
        sort($this->shortOptions);

        $url = Url::fromString($request->getUrl());

        $url->setFragment(null);

        if ( ! $url->getScheme()) {
            $url = Url::fromString('http://' . (string)$url);
        }

        $this->addCommandPart("'" . addcslashes((string)$url, "'") . "'");

        if ($this->shortOptions) {
            $this->addCommandPart('-' . implode('', $this->shortOptions));
        }

        if ($this->options) {
            foreach ($this->options as $name => $value) {
                if (is_array($value)) {
                    foreach ($value as $subValue) {
                        $this->addCommandPart("-{$name}={$subValue}");
                    }
                } else {
                    $this->addCommandPart("-{$name}={$value}");
                }
            }
        }

        return $this->command;
    }

    /**
     * @param $name
     * @param null $value
     */
    protected function addOption($name, $value = null)
    {
        if (strlen($name) === 1 && $value === null) {
            $this->shortOptions[] = $name;
        } else {
            if (isset($this->options[$name])) {
                if ( ! is_array($this->options[$name])) {
                    $this->options[$name] = (array)$this->options[$name];
                }

                $this->options[$name][] = $value;
            } else {
                $this->options[$name] = $value;
            }
        }
    }

    /**
     * @param $part
     */
    protected function addCommandPart($part)
    {
        $this->command .= ' ';

        if (strlen($this->command) + strlen($part) > self::LINE_LENGTH_LIMIT) {
            $this->curLineLength = 0;
            $this->command      .= "\\\n";
        }

        $this->command       .= $part;
        $this->curLineLength += strlen($part) + 1;
    }

}
