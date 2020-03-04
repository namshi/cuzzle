<?php

require '../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Namshi\Cuzzle\Middleware\CurlArrayFormatterMiddleware;
use Namshi\Cuzzle\Middleware\CurlFormatterMiddleware;

$logger = new Logger('guzzele.to.curl'); //initialize the logger
$testHandler = new TestHandler(); //test logger handler
$logger->pushHandler($testHandler);

$handlerStack = HandlerStack::create();
$handlerStack->push(new CurlFormatterMiddleware($logger), 'logger');
//$handlerStack->push(new CurlArrayFormatterMiddleware($logger, 'info'), 'logger');
//$handlerStack->before('prepare_body', new CurlArrayFormatterMiddleware($logger, 'info'), 'logger');
$client = new Client([ 'handler' => $handlerStack ]); //initialize a Guzzle client

$response = $client->get('http://httpbin.org'); //let's fire a request
/*$response = $client->post('http://httpbin.org/post', [
	'form_params' => [
		'a' => 'a',
		'b' => 'b',
	]
]); //let's fire a request*/

var_dump($testHandler->getRecords()); //check the cURL request in the logs :)