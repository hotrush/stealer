<?php

namespace Hotrush\Stealer\Tests;

use Hotrush\Stealer\Config;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    public function testConfigLoad()
    {
        $this->assertFalse(getenv('FOO'));
        Config::load(__DIR__.'/.env');
        $this->assertEquals('bar', getenv('FOO'));
    }
}