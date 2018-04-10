<?php

namespace Hotrush\Stealer\Tests;

use PHPUnit\Framework\TestCase;

class JobTest extends TestCase
{
    public function testJobCreating()
    {
        $loop = \React\EventLoop\Factory::create();
        $logger = $this->getMockBuilder(\Monolog\Logger::class)->setConstructorArgs(['test'])->getMock();
        $clientAbstract = $this->getMockForAbstractClass(\Hotrush\Stealer\AbstractClient::class, [$loop, $logger]);
        $adaptersRegistry = $this->getMockBuilder(\Hotrush\Stealer\AdaptersRegistry::class)->getMock();
        $spiderAbstract = $this->getMockForAbstractClass(\Hotrush\Stealer\Spider\SpiderAbstract::class, [$clientAbstract, $adaptersRegistry]);

        $this->assertTrue($spiderAbstract instanceof \Hotrush\Stealer\Spider\SpiderAbstract);

        $time = time();
        $job = new \Hotrush\Stealer\Job($spiderAbstract);
        $this->assertAttributeNotEmpty('id', $job);
        $this->assertAttributeEquals($job->getId(), 'id', $job);
        $this->assertAttributeEquals($spiderAbstract, 'spider', $job);
        $this->assertEquals($spiderAbstract, $job->getSpider());
        $this->assertAttributeNotEmpty('startTime', $job);
        $this->assertAttributeEquals($job->getStartTime(false), 'startTime', $job);
        $this->assertEquals(date(\DateTime::ISO8601, $time), $job->getStartTime());
        $job->initLogger();
        $this->assertAttributeInstanceOf(\Monolog\Logger::class, 'logger', $job);
        $this->assertAttributeInstanceOf(\Monolog\Logger::class, 'logger', $spiderAbstract);
    }
}
