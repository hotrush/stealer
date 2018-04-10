<?php

namespace Hotrush\Stealer\Tests;

use PHPUnit\Framework\TestCase;

class SpiderRegistryTest extends TestCase
{
    /**
     * @var \Hotrush\Stealer\Spider\Registry
     */
    private $registry;

    private $spiderAbstract;

    private $spidersArray = [];

    public function setUp()
    {
        $loop = \React\EventLoop\Factory::create();
        $logger = $this->getMockBuilder(\Monolog\Logger::class)->setConstructorArgs(['test'])->getMock();
        $clientAbstract = $this->getMockForAbstractClass(\Hotrush\Stealer\AbstractClient::class, [$loop, $logger]);
        $adaptersRegistry = $this->getMockBuilder(\Hotrush\Stealer\AdaptersRegistry::class)->getMock();
        $this->spiderAbstract = $this->getMockForAbstractClass(\Hotrush\Stealer\Spider\SpiderAbstract::class, [$clientAbstract, $adaptersRegistry]);

        $this->registry = new \Hotrush\Stealer\Spider\Registry();
        $this->registry->registerSpider('test', $this->spiderAbstract);

        $this->spidersArray = [
            'test' => $this->spiderAbstract,
        ];
    }

    public function testSpidersRegistry()
    {
        $this->assertAttributeEquals($this->spidersArray, 'spiders', $this->registry);
        $this->assertEquals($this->spidersArray, $this->registry->getSpiders());
        $this->assertEquals($this->spiderAbstract, $this->registry->getSpider('test'));
        $this->assertTrue($this->registry->spiderExists('test'));
    }

    public function testGetInvalidException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No spider with name invalid was found');
        $this->registry->getSpider('invalid');
    }

    public function testRegisterExistException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Spider\'s name already exists.');
        $this->registry->registerSpider('test', $this->spiderAbstract);
    }

    public function testRegisterExistException2()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Spider\'s class already exists.');
        $this->registry->registerSpider('test2', $this->spiderAbstract);
    }
}
