<?php

namespace Hotrush\Stealer;

use Monolog\Logger;
use React\EventLoop\LoopInterface;

abstract class AbstractClient
{
    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * AbstractClient constructor.
     * 
     * @param LoopInterface $loop
     * @param Logger $logger
     */
    public function __construct(LoopInterface $loop, Logger $logger)
    {
        $this->loop = $loop;
        $this->logger = $logger;

        $this->createClient();
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

    public function isStopped()
    {
        return true;
    }

    public function start()
    {

    }

    public function end()
    {
        
    }
}
