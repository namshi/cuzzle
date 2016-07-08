# Cuzzle, cURL command from Guzzle requests

[![Build Status](https://travis-ci.org/namshi/cuzzle.svg?branch=master)](https://travis-ci.org/namshi/cuzzle)

This library let's you dump a Guzzle request to a cURL command for debug and log purpose.

## Prerequisites

This library needs PHP 5.5+.

It has been tested using PHP5.5 to PHP7.0 and HHVM.

## Installation

You can install the library directly with composer:
```
composer require namshi/cuzzle
```
(Add `--dev` if you don't need it in production environment)

## Usage

```php

use Namshi\Cuzzle\Formatter\CurlFormatter;
use GuzzleHttp\Message\Request;

$request = new Request('GET', 'example.local');
$options = [];

echo (new CurlFormatter())->format($request, $options);

```

To log the cURL request generated from a Guzzle request, simply add CurlFormatterSubscriber to Guzzle:

```php

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Namshi\Cuzzle\Middleware\CurlFormatterMiddleware;
use Monolog\Logger;
use Monolog\Handler\TestHandler;

$logger = new Logger('guzzle.to.curl'); //initialize the logger
$testHandler = new TestHandler(); //test logger handler
$logger->pushHandler($testHandler);

$handler = HandlerStack::create();
$handler->after('cookies', new CurlFormatterMiddleware($logger)); //add the cURL formatter middleware
$client  = new Client(['handler' => $handler]); //initialize a Guzzle client

$response = $client->get('http://google.com'); //let's fire a request

var_dump($testHandler->getRecords()); //check the cURL request in the logs, 
//you should see something like: "curl 'http://google.com' -H 'User-Agent: Guzzle/4.2.1 curl/7.37.1 PHP/5.5.16"

```

## Tests

You can run tests locally with

```
phpunit
```

## Feedback

Add an issue, open a PR, drop us an email! We would love to hear from you!
