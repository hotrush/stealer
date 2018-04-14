<?php

namespace Hotrush\Stealer\Middleware;

use Psr\Http\Message\RequestInterface;

class ProxyMiddleware
{
    private $nextHandler;

    private $address;

    public function __construct(callable $nextHandler, string $address)
    {
        $this->nextHandler = $nextHandler;
        $this->address = $address;
    }

    public function __invoke(RequestInterface $request, array $options)
    {
        $fn = $this->nextHandler;

        $options['proxy'] = $this->address;

        return $fn($request, $options);
    }
}
