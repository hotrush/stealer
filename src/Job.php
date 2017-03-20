<?php

namespace Hotrush\Stealer;

use Hotrush\Stealer\Spider\SpiderAbstract;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Job
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var int
     */
    private $startTime;

    /**
     * @var SpiderAbstract
     */
    private $spider;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * Job constructor.
     *
     * @param SpiderAbstract $spider
     */
    public function __construct(SpiderAbstract $spider)
    {
        $this->spider = $spider;
        $this->id = $this->generateId();
        $this->startTime = time();
    }

    /**
     * @return string
     */
    private function generateId()
    {
        return sha1(microtime());
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return bool|string
     */
    public function getStartTime($iso = true)
    {
        return $iso ? date(\DateTime::ISO8601, $this->startTime) : $this->startTime;
    }

    /**
     * Init logger for job and spider
     */
    public function initLogger()
    {
        $this->logger = new Logger('job-'.$this->id);
        $this->logger->pushHandler(
            new StreamHandler(getenv('LOG_DIR').$this->id.'.log')
        );
        $this->spider->setLogger($this->logger);
    }

    /**
     * @return SpiderAbstract
     */
    public function getSpider()
    {
        return $this->spider;
    }

    /**
     * Execute tick jobs
     */
    public function executeTickJob()
    {
        $this->getSpider()->executeTickRequests();
    }
}
