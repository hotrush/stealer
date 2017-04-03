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
        $this->assertEquals(0, $statistic->getActiveRequests());
        $statistic->incrementFailedRequests();
        $this->assertAttributeEquals(1, 'failedRequests', $statistic);
        $statistic->incrementFailedRequests(2);
        $this->assertAttributeEquals(3, 'failedRequests', $statistic);
        $this->assertEquals(3, $statistic->getFailedRequests());
        $statistic->incrementSuccessRequests();
        $this->assertAttributeEquals(1, 'successRequests', $statistic);
        $statistic->incrementSuccessRequests(2);
        $this->assertAttributeEquals(3, 'successRequests', $statistic);
        $this->assertEquals(3, $statistic->getSuccessRequests());
        $this->assertEquals(0, $statistic->getRequestsPerSecond());
    }

    public function testRequestsPerSecond()
    {
        $time = time() - 2;
        $statistic = new \Hotrush\Stealer\Spider\Statistic($time);
        $statistic->incrementTotalRequests(9);
        $statistic->incrementSuccessRequests(3);
        $statistic->incrementFailedRequests(1);
        $this->assertEquals(2, $statistic->getRequestsPerSecond());
    }
}
