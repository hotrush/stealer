<?php

namespace Hotrush\Stealer\Adapters;

use React\MySQL\Connection;
use React\EventLoop\LoopInterface;
use Hotrush\Stealer\AdapterInterface;

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
            'host'   => getenv('MYSQL_HOST'),
            'port'   => getenv('MYSQL_PORT'),
            'dbname' => getenv('MYSQL_DATABASE'),
            'user'   => getenv('MYSQL_USER'),
            'passwd' => getenv('MYSQL_PASSWORD'),
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
