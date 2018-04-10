<?php

namespace Hotrush\Stealer\Tests;

use PHPUnit\Framework\TestCase;

class SpiderRequestTest extends TestCase
{
    public function testSpiderRequestCreating()
    {
        $loop = \React\EventLoop\Factory::create();
        $success = function (\Psr\Http\Message\ResponseInterface $response) {
            $this->assertEquals(200, $response->getStatusCode());
        };
        $failed = function (\Exception $reason) use ($loop) {
            $this->assertEquals(500, $reason->getCode());
            $loop->stop();
        };
        $spiderRequest = new \Hotrush\Stealer\Spider\Request('GET', 'https://httpbin.org/', [], $success, $failed);
        $this->assertAttributeEquals('GET', 'method', $spiderRequest);
        $this->assertAttributeEquals('https://httpbin.org/', 'uri', $spiderRequest);
        $this->assertAttributeEquals($success, 'callback', $spiderRequest);
        $this->assertAttributeEquals($failed, 'errorCallback', $spiderRequest);
        $logger = $this->getMockBuilder(\Monolog\Logger::class)->setConstructorArgs(['test'])->getMock();
        $client = new \Hotrush\Stealer\Client\Guzzle($loop, $logger);
        $spiderRequest->send($client);
        $spiderRequest = new \Hotrush\Stealer\Spider\Request('GET', 'https://httpbin.org/status/500', [], $success, $failed);
        $spiderRequest->send($client);
        $loop->run();
    }
}
