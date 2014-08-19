<?php

require '../vendor/autoload.php';

$client  = new \GuzzleHttp\Client();
$client->getEmitter()->attach(new Namshi\Guzzle\Subscriber\CurlFormatter());

$response = $client->get('http://google.com');



