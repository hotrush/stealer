<?php

namespace Hotrush\Stealer;

use Hotrush\Stealer\Spider\SpiderAbstract;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

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
     * @var LoggerInterface
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
     * @param bool $iso
     *
     * @return bool|string
     */
    public function getStartTime($iso = true)
    {
        return $iso ? date(\DateTime::ISO8601, $this->startTime) : $this->startTime;
    }

    /**
     * Init logger for job and spider.
     */
    public function initLogger()
    {
        $logFileName = sprintf(
            '%s-%s-%s.log',
            $this->spider->getName(),
            (new \DateTime())->format('Y-m-d'),
            $this->id
        );
        $this->logger = new Logger('job-'.$this->id);
        $this->logger->pushHandler(
            new StreamHandler(Config::getenv('LOG_DIR').$logFileName)
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
     * Execute tick jobs.
     */
    public function executeTickJob()
    {
        $this->getSpider()->executeTickRequests();
    }
}
