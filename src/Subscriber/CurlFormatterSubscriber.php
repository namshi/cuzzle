<?php

namespace Namshi\Cuzzle\Subscriber;

use GuzzleHttp\Event\BeforeEvent;
use GuzzleHttp\Event\RequestEvents;
use GuzzleHttp\Event\SubscriberInterface;
use Namshi\Cuzzle\Formatter\CurlFormatter;
use Psr\Log\LoggerInterface;

/**
 * Class CurlFormatterSubscriber subscriber
 * it allow to attach the CurlFormatter to a Guzzle Request
 * @package Namshi\Cuzzle\Subscriber
 */
class CurlFormatterSubscriber implements SubscriberInterface
{
    protected $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function getEvents()
    {
        return [
            'before' => ['onBefore', RequestEvents::EARLY]
        ];
    }

    public function onBefore(BeforeEvent $event)
    {
        $curlCommand = (new CurlFormatter())->format($event->getRequest());
        $this->logger->info($curlCommand);
    }
}
