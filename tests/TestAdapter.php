<?php

namespace Hotrush\Stealer\Tests;

class TestAdapter implements \Hotrush\Stealer\AdapterInterface
{
    private $adapter;

    public function __construct()
    {
        $this->adapter = new \stdClass();
    }

    public function getAdapter()
    {
        return $this->adapter;
    }
}