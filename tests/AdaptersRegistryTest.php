<?php

namespace Hotrush\Stealer\Tests;

class AdaptersRegistryTest extends \PHPUnit_Framework_TestCase
{
    public function testAdapterMethods()
    {
        $adaptersRegistry = new \Hotrush\Stealer\AdaptersRegistry();
        $this->assertAttributeEquals([], 'adapters', $adaptersRegistry);
        $adapter = $this->getMockBuilder(\Hotrush\Stealer\AdapterInterface::class)->getMock();
        $stdClass = new \stdClass();
        $adapter->method('getAdapter')->willReturn($stdClass);
        $adaptersRegistry->addAdapter('test', $adapter);
        $this->assertAttributeEquals([
            'test' => $adapter,
        ], 'adapters', $adaptersRegistry);
        $this->assertEquals($stdClass, $adaptersRegistry->getAdapter('test'));
    }

    public function testGetInvalidAdapter()
    {
        $adaptersRegistry = new \Hotrush\Stealer\AdaptersRegistry();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Adapter name not exists.');
        $adaptersRegistry->getAdapter('invalid');
    }

    public function testSetInvalidAdapter()
    {
        $adaptersRegistry = new \Hotrush\Stealer\AdaptersRegistry();
        $adapter = $this->getMockBuilder(\Hotrush\Stealer\AdapterInterface::class)->getMock();
        $adaptersRegistry->addAdapter('test', $adapter);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Adapter name already exists.');
        $adaptersRegistry->addAdapter('test', $adapter);
    }

    public function testSetInvalidAdapter2()
    {
        $adaptersRegistry = new \Hotrush\Stealer\AdaptersRegistry();
        $adapter = $this->getMockBuilder(\Hotrush\Stealer\AdapterInterface::class)->getMock();
        $adaptersRegistry->addAdapter('test', $adapter);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Adapter instance already exists.');
        $adaptersRegistry->addAdapter('test2', $adapter);
    }
}
