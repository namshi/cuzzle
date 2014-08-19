Gurl, dump a Guzzle request to a cURL  command
================================================

This library let's you dump a Guzzle request to a cURL command for debug and log purpose.

```php

use Namshi\Guzzle\Formatter\CurlShellFormatter;
use GuzzleHttp\Message\Request;

$request = new Request('GET', 'example.local');

echo new CurlShellFormatter())->format($request);

```

or

```php

require '../vendor/autoload.php';

$client  = new \GuzzleHttp\Client();
$client->getEmitter()->attach(new Namshi\Guzzle\Subscriber\CurlFormatter());

$response = $client->get('http://google.com');

//output: curl 'http://google.com' -H='Host: google.com' -H='User-Agent: Guzzle/4.2.0 curl/7.37.1 PHP/5.5.15'

```