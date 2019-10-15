<?php

namespace Hotrush\Stealer;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;

abstract class AbstractClient
{
    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * AbstractClient constructor.
     *
     * @param LoopInterface   $loop
     * @param LoggerInterface $logger
     * @param array           $config
     */
    public function __construct(LoopInterface $loop, LoggerInterface $logger, array $config = [])
    {
        $this->loop = $loop;
        $this->logger = $logger;
        $this->config = $config;
        $this->client = $this->createClient();
    }

    abstract protected function createClient();

    /**
     * @return \GuzzleHttp\Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @return bool
     */
    public function isReady(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isStopped(): bool
    {
        return true;
    }

    /**
     * Start the client.
     */
    public function start(): void
    {
    }

    /**
     * Stops the client.
     */
    public function stop(): void
    {
    }
}
