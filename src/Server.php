<?php

namespace Hotrush\Stealer;

use Hotrush\Stealer\Spider\Registry;
use Monolog\Logger;
use React\EventLoop\LoopInterface;
use React\Http\Request;
use React\Http\Response;
use React\Http\Server as HttpServer;
use React\Socket\Server as SocketServer;

class Server
{
    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var Worker
     */
    private $worker;

    /**
     * @var string
     */
    private $port = '8080';

    /**
     * Server constructor.
     *
     * @param LoopInterface $loop
     * @param Registry      $registry
     * @param Logger        $logger
     * @param Worker        $worker
     */
    public function __construct(LoopInterface $loop, Registry $registry, Logger $logger, Worker $worker)
    {
        $this->loop = $loop;
        $this->registry = $registry;
        $this->logger = $logger;
        $this->worker = $worker;
    }

    /**
     * Change api server port.
     *
     * @param $port
     */
    public function setPort($port)
    {
        $this->port = $port;
    }

    /**
     * Start an api server.
     *
     * @throws \React\Socket\ConnectionException
     */
    public function start()
    {
        $socket = new SocketServer($this->loop);
        $http = new HttpServer($socket);
        $api = new Api($this->registry, $this->logger, $this->worker);
        $http->on('request', function (Request $request, Response $response) use ($api) {
            $api->processRequest($request, $response);
        });
        $socket->listen($this->port);
    }
}
