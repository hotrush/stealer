<?php

namespace Hotrush\Stealer\Tests;

use PHPUnit\Framework\TestCase;

class SpiderRegistryTest extends TestCase
{
    /**
     * @var \Hotrush\Stealer\Spider\Registry
     */
    private $registry;

    private $spiderAbstractClass = \Hotrush\Stealer\Spider\SpiderAbstract::class;

    private $spidersArray = [];

    public function setUp()
    {
        $this->registry = new \Hotrush\Stealer\Spider\Registry();
        $this->registry->registerSpider('test', $this->spiderAbstractClass);

        $this->spidersArray = [
            'test' => \Hotrush\Stealer\Spider\SpiderAbstract::class,
        ];
    }

    public function testSpidersRegistry()
    {
        $this->assertAttributeEquals($this->spidersArray, 'spiders', $this->registry);
        $this->assertEquals($this->spidersArray, $this->registry->getSpiders());
        $this->assertEquals($this->spiderAbstractClass, $this->registry->getSpider('test'));
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
        $this->registry->registerSpider('test', $this->spiderAbstractClass);
    }

    public function testRegisterExistException2()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Spider\'s class already exists.');
        $this->registry->registerSpider('test2', $this->spiderAbstractClass);
    }
}
