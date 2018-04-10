<?php

namespace Hotrush\Stealer;

use Hotrush\Stealer\Middleware\ProxyMiddleware;
use Hotrush\Stealer\Middleware\UserAgentMiddleware;
use Hotrush\Stealer\Middleware\RandomUserAgentMiddleware;

class Middleware
{
    /**
     * @param $address
     *
     * @return \Closure
     */
    public static function proxy($address)
    {
        return function (callable $handler) use ($address) {
            return new ProxyMiddleware($handler, $address);
        };
    }

    /**
     * @return \Closure
     */
    public static function userAgent()
    {
        return function (callable $handler) {
            return new UserAgentMiddleware($handler);
        };
    }

    public static function randomUserAgent()
    {
        return function (callable $handler) {
            return new RandomUserAgentMiddleware($handler);
        };
    }
}
