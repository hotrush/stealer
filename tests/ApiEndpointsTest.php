<?php

namespace Hotrush\Stealer\Tests;

use PHPUnit\Framework\TestCase;

class ApiEndpointsTest extends TestCase
{
    public function testBaseApiEndpoint()
    {
        $loop = \React\EventLoop\Factory::create();
        $adaptersRegistry = $this->getMockBuilder(\Hotrush\Stealer\AdaptersRegistry::class)->getMock();
        $logger = $this->getMockBuilder(\Monolog\Logger::class)->setConstructorArgs(['test'])->getMock();
        $clientAbstract = $this->getMockForAbstractClass(\Hotrush\Stealer\AbstractClient::class, [$loop, $logger]);
        $worker = $this->getMockBuilder(\Hotrush\Stealer\Worker::class)->setConstructorArgs([$loop, $clientAbstract, $adaptersRegistry, $logger])->getMock();
        $registry = $this->getMockBuilder(\Hotrush\Stealer\Spider\Registry::class)->getMock();

        $baseEndpoint = new \Hotrush\Stealer\ApiEndpoints\BaseEndpoint($registry, $worker);

        $this->assertAttributeInstanceOf(\Hotrush\Stealer\Worker::class, 'worker', $baseEndpoint);
        $this->assertAttributeInstanceOf(\Hotrush\Stealer\Spider\Registry::class, 'registry', $baseEndpoint);
    }

    public function testListJobEndpoint()
    {
        $loop = \React\EventLoop\Factory::create();
        $adaptersRegistry = $this->getMockBuilder(\Hotrush\Stealer\AdaptersRegistry::class)->getMock();
        $logger = $this->getMockBuilder(\Monolog\Logger::class)->setConstructorArgs(['test'])->getMock();
        $clientAbstract = $this->getMockForAbstractClass(\Hotrush\Stealer\AbstractClient::class, [$loop, $logger]);
        $worker = new \Hotrush\Stealer\Worker($loop, $clientAbstract, $adaptersRegistry, $logger);
        $registry = new \Hotrush\Stealer\Spider\Registry();

        $listJobsEndpoint = new \Hotrush\Stealer\ApiEndpoints\ListJobsEndpoint($registry, $worker);

        /** @var \React\Http\Response $response */
        $response = call_user_func($listJobsEndpoint, []);

        $this->assertInstanceOf(\React\Http\Response::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('{"active_jobs":[]}', $response->getBody()->getContents());
        $this->assertEquals(['application/json'], $response->getHeader('content-type'));
    }

    public function testCancelJobEndpoint()
    {
        $loop = \React\EventLoop\Factory::create();
        $adaptersRegistry = $this->getMockBuilder(\Hotrush\Stealer\AdaptersRegistry::class)->getMock();
        $logger = $this->getMockBuilder(\Monolog\Logger::class)->setConstructorArgs(['test'])->getMock();
        $clientAbstract = $this->getMockForAbstractClass(\Hotrush\Stealer\AbstractClient::class, [$loop, $logger]);
        $worker = new \Hotrush\Stealer\Worker($loop, $clientAbstract, $adaptersRegistry, $logger);
        $registry = new \Hotrush\Stealer\Spider\Registry();

        $cancelJobEndpoint = new \Hotrush\Stealer\ApiEndpoints\CancelJobEndpoint($registry, $worker);

        /** @var \React\Http\Response $response */
        $response = call_user_func($cancelJobEndpoint, []);

        $this->assertInstanceOf(\React\Http\Response::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('{"message":"No job id provided."}', $response->getBody()->getContents());
        $this->assertEquals(['application/json'], $response->getHeader('content-type'));

        /** @var \React\Http\Response $response */
        $response = call_user_func($cancelJobEndpoint, ['id' => 'foo']);

        $this->assertInstanceOf(\React\Http\Response::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('{"message":"No job with id foo was found"}', $response->getBody()->getContents());
        $this->assertEquals(['application/json'], $response->getHeader('content-type'));

        // @todo test cancel
    }

    public function testScheduleJobEndpoint()
    {
        $loop = \React\EventLoop\Factory::create();
        $adaptersRegistry = $this->getMockBuilder(\Hotrush\Stealer\AdaptersRegistry::class)->getMock();
        $logger = $this->getMockBuilder(\Monolog\Logger::class)->setConstructorArgs(['test'])->getMock();
        $clientAbstract = $this->getMockForAbstractClass(\Hotrush\Stealer\AbstractClient::class, [$loop, $logger]);
        $worker = new \Hotrush\Stealer\Worker($loop, $clientAbstract, $adaptersRegistry, $logger);
        $registry = new \Hotrush\Stealer\Spider\Registry();

        $scheduleJobEndpoint = new \Hotrush\Stealer\ApiEndpoints\ScheduleJobEndpoint($registry, $worker);

        /** @var \React\Http\Response $response */
        $response = call_user_func($scheduleJobEndpoint, []);

        $this->assertInstanceOf(\React\Http\Response::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('{"message":"No spider found."}', $response->getBody()->getContents());
        $this->assertEquals(['application/json'], $response->getHeader('content-type'));

        // @todo test scheduling
    }
}
