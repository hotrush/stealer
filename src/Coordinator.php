<?php

namespace Hotrush\Stealer;

use Hotrush\Stealer\Spider\Registry;
use MKraemer\ReactPCNTL\PCNTL;
use Monolog\Logger;
use React\EventLoop\LoopInterface;

class Coordinator
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var Server
     */
    private $server;

    /**
     * @var AbstractClient
     */
    private $client;

    /**
     * @var Worker
     */
    private $worker;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var AdaptersRegistry
     */
    private $adaptersRegistry;

    public function __construct(Registry $registry, LoopInterface $loop, AbstractClient $client, AdaptersRegistry $adaptersRegistry, Logger $logger)
    {
        $this->registry = $registry;
        $this->loop = $loop;
        $this->client = $client;
        $this->logger = $logger;
        $this->adaptersRegistry = $adaptersRegistry;
        $this->worker = new Worker($this->loop, $this->client, $this->adaptersRegistry, $this->logger);
        $this->server = new Server($this->loop, $this->registry, $this->logger, $this->worker);
        $this->registerKillSignal();
    }

    /**
     * @return Server
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * Start all the magic!
     */
    public function run()
    {
        $this->server->start();
        $this->loop->run();
    }

    /**
     * Register handlers for killing signals.
     * Close worker and client on exit.
     */
    private function registerKillSignal()
    {
        $pcntl = new PCNTL($this->loop);
        $onceReceived = false;
        $handler = function () use (&$onceReceived) {
            if ($onceReceived === true) {
                $this->logger->info('Force exit!');
                exit;
            }
            $onceReceived = true;
            $this->logger->info('Stopping stealer');
            $this->worker->stop();
            $this->client->end();
            $this->loop->addPeriodicTimer(1, function () {
                if (
                    $this->worker->isStopped()
                    &&
                    $this->client->isStopped()
                ) {
                    exit;
                }
            });
        };

        $pcntl->on(SIGINT, $handler);
        $pcntl->on(SIGTERM, $handler);
        $pcntl->on(SIGHUP, $handler);
    }
}
