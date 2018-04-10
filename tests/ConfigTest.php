<?php

namespace Hotrush\Stealer\Tests;

use Hotrush\Stealer\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testConfigLoad()
    {
        $this->assertFalse(getenv('FOO'));
        Config::load(__DIR__.'/etc/.env');
        $this->assertEquals('bar', getenv('FOO'));
    }

    public function testLogsDir()
    {
        $this->assertFalse(getenv('LOG_DIR'));
        Config::setLogsDir('testDir');
        $this->assertEquals('testDir', getenv('LOG_DIR'));
    }

    public function testLoadAdapters()
    {
        $loop = \React\EventLoop\Factory::create();
        $adaptersRegistry = Config::loadAdapters(__DIR__.'/etc/adapters.php', $loop);
        $this->assertInstanceOf(\Hotrush\Stealer\AdaptersRegistry::class, $adaptersRegistry);
        $this->assertAttributeNotEmpty('adapters', $adaptersRegistry);
        $this->assertInstanceOf(\stdClass::class, $adaptersRegistry->getAdapter('test'));
    }

    public function testLoadRegistry()
    {
        $registry = Config::loadRegistry(__DIR__.'/etc/spiders.php');
        $this->assertInstanceOf(\Hotrush\Stealer\Spider\Registry::class, $registry);
        $this->assertTrue($registry->spiderExists('test'));
    }
}
