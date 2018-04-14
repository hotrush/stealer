<?php

namespace Hotrush\Stealer;

use Hotrush\Stealer\Middleware\ProxyMiddleware;
use Hotrush\Stealer\Middleware\RandomUserAgentMiddleware;
use Hotrush\Stealer\Middleware\UserAgentMiddleware;

class Middleware
{
    /**
     * @param $address
     *
     * @return callable
     */
    public static function proxy(string $address): callable
    {
        return function (callable $handler) use ($address) {
            return new ProxyMiddleware($handler, $address);
        };
    }

    /**
     * @return callable
     */
    public static function userAgent(): callable
    {
        return function (callable $handler) {
            return new UserAgentMiddleware($handler);
        };
    }

    /**
     * @return callable
     */
    public static function randomUserAgent(): callable
    {
        return function (callable $handler) {
            return new RandomUserAgentMiddleware($handler);
        };
    }
}
