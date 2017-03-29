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
     * @var AdaptersRegistry
     */
    private $adaptersRegistry;

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
     * @var int
     */
    private $statsLoggingInterval = 600;

    /**
     * Worker constructor.
     *
     * @param LoopInterface    $loop
     * @param AbstractClient   $client
     * @param AdaptersRegistry $adaptersRegistry
     * @param Logger           $logger
     */
    public function __construct(LoopInterface $loop, AbstractClient $client, AdaptersRegistry $adaptersRegistry, Logger $logger)
    {
        $this->loop = $loop;
        $this->client = $client;
        $this->adaptersRegistry = $adaptersRegistry;
        $this->logger = $logger;
        $this->startPeriodicTimer();
        $this->startStatsPeriodicTimer();
    }

    /**
     * @param $spiderName
     *
     * @return string
     */
    public function runSpiderJob($spiderName)
    {
        $spider = new $spiderName($this->client, $this->adaptersRegistry);
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
                    $this->logJobStats($job);
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
     * Start timer for logging stats for running jobs.
     */
    private function startStatsPeriodicTimer()
    {
        $this->loop->addPeriodicTimer($this->statsLoggingInterval, function () {
            foreach ($this->activeJobs as $job) {
                if ($job->getSpider()->isActive()) {
                    $this->logger->info('Job in progress. ID: '.$job->getId());
                    $this->logJobStats($job);
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

    /**
     * @param Job $job
     */
    private function logJobStats(Job $job)
    {
        $this->logger->info('Work time: '.(time() - $job->getStartTime(false)).' seconds');
        $this->logger->info('Total requests: '.$job->getSpider()->getStatistic()->getTotalRequests());
        $this->logger->info('Success requests: '.$job->getSpider()->getStatistic()->getSuccessRequests());
        $this->logger->info('Failed requests: '.$job->getSpider()->getStatistic()->getFailedRequests());
        $this->logger->info('Average requests per second: '.$job->getSpider()->getStatistic()->getRequestsPerSecond());
    }

    /**
     * @param $id
     *
     * @return bool
     */
    public function hasActiveJob($id)
    {
        return isset($this->activeJobs[$id]);
    }

    /**
     * Stops running job.
     *
     * @param $id
     */
    public function stopJob($id)
    {
        if (!$this->hasActiveJob($id)) {
            throw new \InvalidArgumentException('No job with id '.$id.' was found');
        }
        $job = $this->activeJobs[$id];
        $this->logger->info('Stopping the job. ID: '.$job->getId());
        $this->logJobStats($job);
        $this->finishedJobs[] = $job;
        unset($this->activeJobs[$id]);
        $this->activeJobs = array_values($this->activeJobs);
        if (!$this->activeJobs) {
            $this->client->end();
        }
    }
}
