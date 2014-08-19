# Cuzzle, cURL command from Guzzle requests

[![Build Status](https://travis-ci.org/namshi/cuzzle.svg?branch=master)](https://travis-ci.org/namshi/cuzzle)

This library let's you dump a Guzzle request to a cURL command for debug and log purpose.

## Prerequisites

This library needs PHP 5.4+.

It has been tested using PHP5.3 to PHP5.6 and HHVM.

## Installation

You can install the library directly with composer:
```
"namshi\cuzzle": "0.1.0"
```


## Usage

```php

use Namshi\Cuzzle\Formatter\CurlFormatter;
use GuzzleHttp\Message\Request;

$request = new Request('GET', 'example.local');

echo (new CurlFormatter())->format($request);

```

To log the cURL request generated from a Guzzle request, simply add CurlFormatterSubscriber to Guzzle:

```php

use GuzzleHttp\Client;
use Namshi\Cuzzle\Subscriber\CurlFormatterSubscriber;
use Monolog\Logger;
use Monolog\Handler\TestHandler;

$logger = new Logger('guzzele.to.curl'); //initialize the logger
$testHandler = new TestHandler(); //test logger handler
$logger->pushHandler($testHandler);

$client  = new Client(); //initialize a Guzzle client
$client->getEmitter()->attach(new CurlFormatterSubscriber($logger)); //add the cURL formatter subscriber

$response = $client->get('http://google.com'); //let's fire a request

var_dump($testHandler->getRecords()); //check the cURL request in the logs, you should see curl 'http://google.com' -H 'User-Agent: Guzzle/4.2.1 curl/7.37.1 PHP/5.5.16

```

## Tests

You can run tests locally with

```
phpunit
```

## Feedback

Add an issue, open a PR, drop us an email! We would love to hear from you!