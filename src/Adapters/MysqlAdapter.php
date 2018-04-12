<?php

namespace Hotrush\Stealer\Adapters;

use Hotrush\Stealer\AdapterInterface;
use Hotrush\Stealer\Config;
use React\EventLoop\LoopInterface;
use React\MySQL\Connection;

class MysqlAdapter implements AdapterInterface
{
    /**
     * @var Connection
     */
    private $adapter;

    /**
     * MysqlAdapter constructor.
     *
     * @param LoopInterface $loop
     */
    public function __construct(LoopInterface $loop)
    {
        $this->adapter = new Connection($loop, [
            'host'   => Config::getenv('MYSQL_HOST'),
            'port'   => Config::getenv('MYSQL_PORT'),
            'dbname' => Config::getenv('MYSQL_DATABASE'),
            'user'   => Config::getenv('MYSQL_USER'),
            'passwd' => Config::getenv('MYSQL_PASSWORD'),
        ]);
    }

    /**
     * @return Connection
     */
    public function getAdapter()
    {
        return $this->adapter;
    }
}
