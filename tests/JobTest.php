<?php

namespace Hotrush\Stealer\Tests;

use Hotrush\Stealer\AbstractClient;
use Hotrush\Stealer\Job;
use Hotrush\Stealer\Spider\SpiderAbstract;

class JobTest extends \PHPUnit_Framework_TestCase
{
    public function testJobCreating()
    {
        $loop = \React\EventLoop\Factory::create();
        $logger = $this->getMockBuilder(\Monolog\Logger::class)->setConstructorArgs(['test'])->getMock();
        $clientAbstract = $this->getMockForAbstractClass(AbstractClient::class, [$loop, $logger]);
        $spiderAbstract = $this->getMockForAbstractClass(SpiderAbstract::class, [$clientAbstract]);

        $this->assertTrue($spiderAbstract instanceof SpiderAbstract);

        $time = time();
        $job = new Job($spiderAbstract);
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