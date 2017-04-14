<?php

namespace Hotrush\Stealer\Middleware;

use Campo\UserAgent;
use Psr\Http\Message\RequestInterface;

class RandomUserAgentMiddleware
{
    private $nextHandler;

    public function __construct(callable $nextHandler)
    {
        $this->nextHandler = $nextHandler;
    }

    public function __invoke(RequestInterface $request, array $options)
    {
        $fn = $this->nextHandler;

        $request = $request->withHeader('User-Agent', UserAgent::random());

        return $fn($request, $options);
    }
}
