<?php

namespace Hotrush\Stealer;

use Hotrush\Stealer\Spider\Registry;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\Http\Server as HttpServer;

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
     * @var Worker
     */
    private $worker;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Server constructor.
     *
     * @param LoopInterface   $loop
     * @param Registry        $registry
     * @param Worker          $worker
     * @param LoggerInterface $logger
     */
    public function __construct(LoopInterface $loop, Registry $registry, Worker $worker, LoggerInterface $logger)
    {
        $this->loop = $loop;
        $this->registry = $registry;
        $this->worker = $worker;
        $this->logger = $logger;
    }

    public function start(): void
    {
        $api = new Api($this->registry, $this->worker, $this->logger);

        $server = new HttpServer(function (ServerRequestInterface $request) use ($api) {
            return $api->dispatchRequest($request);
        });

        $socket = new \React\Socket\Server(Config::getenv('SERVER_PORT'), $this->loop);
        $server->listen($socket);
    }
}
