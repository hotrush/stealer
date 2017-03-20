<?php

namespace Hotrush\Stealer\Middleware;

use Psr\Http\Message\RequestInterface;

class UserAgentMiddleware
{
    private $nextHandler;

    public function __construct(callable $nextHandler)
    {
        $this->nextHandler = $nextHandler;
    }

    public function __invoke(RequestInterface $request, array $options)
    {
        $fn = $this->nextHandler;

        $request->withHeader('User-Agent', 'Stealer 0.0.0');

        return $fn($request, $options);
    }
}
