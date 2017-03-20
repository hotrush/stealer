<?php

namespace Hotrush\Stealer\Client;

use Hotrush\Stealer\AbstractClient;
use Hotrush\Stealer\Middleware;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use React\Dns\Resolver\Factory;
use WyriHaximus\React\GuzzlePsr7\HttpClientAdapter;

class Guzzle extends AbstractClient
{
    protected function createClient()
    {
        $dnsResolverFactory = new Factory();
        $dnsResolver = $dnsResolverFactory->createCached('8.8.8.8', $this->loop);

        $handler = new HttpClientAdapter($this->loop, null, $dnsResolver);
        $stack = HandlerStack::create($handler);
        $stack->push(Middleware::userAgent());
        $this->client = new GuzzleClient([
            'handler' => $stack,
        ]);
    }
}
