<?php

namespace Hotrush\Stealer\Middleware;

use Hotrush\Stealer\App;
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

        $request->withHeader('User-Agent', sprintf('Stealer %s', App::$version));

        return $fn($request, $options);
    }
}
