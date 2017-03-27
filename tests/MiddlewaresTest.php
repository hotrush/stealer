<?php

namespace Hotrush\Stealer\Tests;

class MiddlewaresTest extends \PHPUnit_Framework_TestCase
{
    public function testProxyMiddlewaresAdding()
    {
        $proxyMiddleware = \Hotrush\Stealer\Middleware::proxy('127.0.0.1');
        $this->assertTrue(is_callable($proxyMiddleware));
        $result = $proxyMiddleware(function() {
        });
        $this->assertInstanceOf(\Hotrush\Stealer\Middleware\ProxyMiddleware::class, $result);
        $this->assertAttributeEquals('127.0.0.1', 'address', $result);
    }

    public function testUserAgentMiddlewaresAdding()
    {
        $userAgentMiddleware = \Hotrush\Stealer\Middleware::userAgent();
        $this->assertTrue(is_callable($userAgentMiddleware));
        $result = $userAgentMiddleware(function() {
        });
        $this->assertInstanceOf(\Hotrush\Stealer\Middleware\UserAgentMiddleware::class, $result);
    }

    public function testProxyMiddleware()
    {
        $m = new \Hotrush\Stealer\Middleware\ProxyMiddleware(function ($request, $options) {
            $this->assertEquals('127.0.0.1', $options['proxy']);
        }, '127.0.0.1');

        $request = $this->getMockBuilder(\Psr\Http\Message\RequestInterface::class)->getMock();
        $m($request, []);
    }

    public function testUserAgentMiddleware()
    {
        $m = new \Hotrush\Stealer\Middleware\UserAgentMiddleware(function (\Psr\Http\Message\RequestInterface $request, $options) {
            $this->assertEquals('Stealer 0.0.0', $request->getHeader('User-Agent'));
        });

        $request = $this->getMockBuilder(\Psr\Http\Message\RequestInterface::class)->getMock();
        $request->method('getHeader')->with('User-Agent')->willReturn('Stealer 0.0.0');
        $m($request, []);
    }
}
