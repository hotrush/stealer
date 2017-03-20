<?php

namespace Hotrush\Stealer\Tests;

class SpiderStatisticTest extends \PHPUnit_Framework_TestCase
{
    public function testStatistic()
    {
        $time = time();
        $statistic = new \Hotrush\Stealer\Spider\Statistic($time);
        $this->assertAttributeEquals($time, 'startTime', $statistic);
        $this->assertAttributeEquals(0, 'totalRequests', $statistic);
        $this->assertAttributeEquals(0, 'activeRequests', $statistic);
        $this->assertAttributeEquals(0, 'successRequests', $statistic);
        $this->assertAttributeEquals(0, 'failedRequests', $statistic);
        $statistic->incrementTotalRequests();
        $this->assertAttributeEquals(1, 'totalRequests', $statistic);
        $statistic->incrementTotalRequests(2);
        $this->assertAttributeEquals(3, 'totalRequests', $statistic);
        $this->assertEquals(3, $statistic->getTotalRequests());
        $statistic->incrementActiveRequests();
        $this->assertAttributeEquals(1, 'activeRequests', $statistic);
        $statistic->incrementActiveRequests(2);
        $this->assertAttributeEquals(3, 'activeRequests', $statistic);
        $statistic->decrementActiveRequests();
        $this->assertAttributeEquals(2, 'activeRequests', $statistic);
        $statistic->decrementActiveRequests(2);
        $this->assertAttributeEquals(0, 'activeRequests', $statistic);
        $statistic->incrementFailedRequests();
        $this->assertAttributeEquals(1, 'failedRequests', $statistic);
        $statistic->incrementFailedRequests(2);
        $this->assertAttributeEquals(3, 'failedRequests', $statistic);
        $statistic->decrementFailedRequests();
        $this->assertAttributeEquals(2, 'failedRequests', $statistic);
        $statistic->decrementFailedRequests(2);
        $this->assertAttributeEquals(0, 'failedRequests', $statistic);
        $this->assertEquals(0, $statistic->getRequestsPerSecond());
    }

    public function testRequestsPerSecond()
    {
        $time = time() - 3;
        $statistic = new \Hotrush\Stealer\Spider\Statistic($time);
        $statistic->incrementTotalRequests(9);
        $this->assertEquals(3, $statistic->getRequestsPerSecond());
    }
}