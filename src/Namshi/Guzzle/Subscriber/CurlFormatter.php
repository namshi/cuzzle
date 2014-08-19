<?php

namespace Namshi\Guzzle\Subscriber;

use GuzzleHttp\Event\RequestEvents;
use GuzzleHttp\Event\SubscriberInterface;
use GuzzleHttp\Event\CompleteEvent;
use Namshi\Guzzle\Formatter\CurlShellFormatter;

/**
 * Class CurlFormatter subscriber
 * @package Namshi\Guzzle\Subscriber
 */
class CurlFormatter implements SubscriberInterface
{

    public function getEvents()
    {
        return [
            'complete' => ['onComplete', RequestEvents::REDIRECT_RESPONSE + 10]
        ];
    }

    public function onComplete(CompleteEvent $event)
    {
        echo PHP_EOL;
        echo (new CurlShellFormatter())->format($event->getRequest());
        echo PHP_EOL;
        die();
    }
}
