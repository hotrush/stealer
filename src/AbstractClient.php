<?php

namespace Hotrush\Stealer;

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
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * AbstractClient constructor.
     *
     * @param LoopInterface     $loop
     * @param LoggerInterface   $logger
     */
    public function __construct(LoopInterface $loop, LoggerInterface $logger)
    {
        $this->loop = $loop;
        $this->logger = $logger;
        $this->client = $this->createClient();
    }

    abstract protected function createClient();

    /**
     * @return \GuzzleHttp\Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return bool
     */
    public function isReady()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isStopped()
    {
        return true;
    }

    /**
     * Start the client
     */
    public function start()
    {
    }

    /**
     * Stops the client
     */
    public function stop()
    {
    }
}
