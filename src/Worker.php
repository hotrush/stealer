<?php

namespace Hotrush\Stealer;

use Monolog\Logger;
use React\EventLoop\LoopInterface;

class Worker
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
     * @var Logger
     */
    private $logger;

    /**
     * @var Job[]
     */
    private $activeJobs = [];

    /**
     * @var Job[]
     */
    private $finishedJobs = [];

    /**
     * @var bool
     */
    private $stopped = false;

    /**
     * @var bool
     */
    private $stopping = false;

    /**
     * Worker constructor.
     *
     * @param LoopInterface  $loop
     * @param AbstractClient $client
     * @param Logger         $logger
     */
    public function __construct(LoopInterface $loop, AbstractClient $client, Logger $logger)
    {
        $this->loop = $loop;
        $this->client = $client;
        $this->logger = $logger;
        $this->startPeriodicTimer();
    }

    /**
     * @param $spiderName
     *
     * @return string
     */
    public function runSpiderJob($spiderName)
    {
        $spider = new $spiderName($this->client);
        $job = new Job($spider);
        $jobId = $job->getId();
        $job->initLogger();
        $this->activeJobs[$jobId] = $job;
        $this->logger->info('Job started. Spider: '.$spiderName.'. ID: '.$jobId);
        return $jobId;
    }

    public function stop()
    {
        $this->stopping = true;
        $this->logger->info('Stopping all jobs');
        if ($this->activeJobs) {
            foreach ($this->activeJobs as $job) {
                // @todo do any job end task
            }
        }
        $this->stopped = true;
        $this->stopping = false;
    }

    /**
     * Start react periodic timer.
     */
    private function startPeriodicTimer()
    {
        $this->loop->addPeriodicTimer(1, function () {
            if ($this->stopping || $this->stopped) {
                return;
            }
            foreach ($this->activeJobs as $key => $job) {
                if ($job->getSpider()->isActive()) {
                    if ($this->client->isReady()) {
                        $job->executeTickJob();
                    } else {
                        $this->client->start();
                    }
                } else {
                    $this->logger->info('Job finished. ID: '.$job->getId());
                    $this->logger->info('Work time: '.(time() - $job->getStartTime(false)));
                    $this->logger->info('Total requests: '.$job->getSpider()->getStatistic()->getTotalRequests());
                    $this->logger->info('Success requests: '.$job->getSpider()->getStatistic()->getSuccessRequests());
                    $this->logger->info('Failed requests: '.$job->getSpider()->getStatistic()->getFailedRequests());
                    $this->logger->info('Average requests per second: '.$job->getSpider()->getStatistic()->getRequestsPerSecond());
                    $this->finishedJobs[] = $job;
                    unset($this->activeJobs[$key]);
                    $this->activeJobs = array_values($this->activeJobs);
                    if (!$this->activeJobs) {
                        $this->client->end();
                    }
                }
            }
        });
    }

    /**
     * @return Job[]
     */
    public function getActiveJobs()
    {
        return $this->activeJobs;
    }

    /**
     * @return Job[]
     */
    public function getFinishedJobs()
    {
        return $this->finishedJobs;
    }

    /**
     * @return bool
     */
    public function isStopped()
    {
        return $this->stopped;
    }
}
