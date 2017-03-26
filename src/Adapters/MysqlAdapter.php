<?php

namespace Hotrush\Stealer\Adapters;

use Hotrush\Stealer\AdapterInterface;
use React\EventLoop\LoopInterface;
use React\MySQL\Connection as MysqlConnection;

class MysqlAdapter implements AdapterInterface
{
    private $adapter;

    public function __construct(LoopInterface $loop)
    {
        $this->adapter = new MysqlConnection($loop, array(
            'dbname' => getenv('MYSQL_DATABASE'),
            'user'   => getenv('MYSQL_USER'),
            'passwd' => getenv('MYSQL_PASSWORD'),
        ));
    }

    public function getAdapter()
    {
        return $this->adapter;
    }
}