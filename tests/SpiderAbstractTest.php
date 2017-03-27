<?php

namespace Hotrush\Stealer\Tests;

class SpiderAbstractTest extends \PHPUnit_Framework_TestCase
{
    public function testSpiderAbstract()
    {
        $loop = \React\EventLoop\Factory::create();
        $logger = $this->getMockBuilder(\Monolog\Logger::class)->setConstructorArgs(['test'])->getMock();
        $client = new \Hotrush\Stealer\Client\Guzzle($loop, $logger);
        $adaptersRegistry = $this->getMockBuilder(\Hotrush\Stealer\AdaptersRegistry::class)->getMock();
        $spider = $this->getMockForAbstractClass(\Hotrush\Stealer\Spider\SpiderAbstract::class, [$client, $adaptersRegistry]);
        $this->assertAttributeEquals($client, 'client', $spider);
        $this->assertAttributeEquals($adaptersRegistry, 'adaptersRegistry', $spider);
        $this->assertAttributeEquals(4, 'perTick', $spider);
        $this->assertAttributeEquals([], 'requests', $spider);
        $this->assertAttributeEquals(null, 'logger', $spider);
        $this->assertAttributeInstanceOf(\Hotrush\Stealer\Spider\Statistic::class, 'statistic', $spider);
        $spider->setLogger($logger);
        $this->assertAttributeEquals($logger, 'logger', $spider);
        $this->assertEquals($client, $spider->getClient());
        $this->assertInstanceOf(\Hotrush\Stealer\Spider\Statistic::class, $spider->getStatistic());
        $this->assertFalse($spider->isActive());
        $response = $this->getMockBuilder(\Psr\Http\Message\ResponseInterface::class)->getMock();
        $response->method('getBody')->willReturn($this->getMockBuilder(\Psr\Http\Message\StreamInterface::class)->getMock());
        $class = new \ReflectionClass(\Hotrush\Stealer\Spider\SpiderAbstract::class);
        $method = $class->getMethod('createCrawlerFromResponse');
        $method->setAccessible(true);
        $this->assertInstanceOf(\Symfony\Component\DomCrawler\Crawler::class, $method->invokeArgs($spider, [$response]));
    }
}
