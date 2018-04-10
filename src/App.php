<?php

namespace Hotrush\Stealer;

use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use Hotrush\Stealer\Spider\Registry;

class App
{
    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var AbstractClient
     */
    private $client;

    /**
     * @var AdaptersRegistry
     */
    private $adaptersRegistry;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Server
     */
    private $server;

    /**
     * @var string
     */
    public static $version = '0.2.0';

    /**
     * App constructor.
     *
     * @param LoopInterface     $loop
     * @param AbstractClient    $client
     * @param AdaptersRegistry  $adaptersRegistry
     * @param Registry          $registry
     * @param LoggerInterface   $logger
     */
    public function __construct(LoopInterface $loop, AbstractClient $client, AdaptersRegistry $adaptersRegistry, Registry $registry, LoggerInterface $logger)
    {
        $this->loop = $loop;
        $this->logger = $logger;
        $this->client = $client;
        $this->adaptersRegistry = $adaptersRegistry;
        $this->registry = $registry;
        $this->worker = new Worker($this->loop, $this->client, $this->adaptersRegistry, $this->logger);
        $this->server = new Server($this->loop, $this->registry, $this->worker, $this->logger);
        $this->registerKillSignal();
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
        $onceReceived = false;
        $handler = function () use (&$onceReceived) {
            if ($onceReceived === true) {
                $this->logger->info('Force exit!');
                exit;
            }
            $onceReceived = true;
            $this->logger->info('Stopping stealer...');
            $this->worker->stop();
            $this->client->stop();
            $this->loop->addPeriodicTimer(1, function () {
                if ($this->worker->isStopped() && $this->client->isStopped()) {
                    $this->logger->info('Stealer stopped. Goodbye!');
                    exit;
                }
            });
        };

        $this->loop->addSignal(SIGINT, $handler);
        $this->loop->addSignal(SIGTERM, $handler);
        $this->loop->addSignal(SIGHUP, $handler);
    }
}
