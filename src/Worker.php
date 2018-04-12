<?php

namespace Hotrush\Stealer;

use Psr\Log\LoggerInterface;
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
     * @var LoggerInterface
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
     * @param LoggerInterface  $logger
     */
    public function __construct(LoopInterface $loop, AbstractClient $client, AdaptersRegistry $adaptersRegistry, LoggerInterface $logger)
    {
        $this->loop = $loop;
        $this->client = $client;
        $this->adaptersRegistry = $adaptersRegistry;
        $this->logger = $logger;
        $this->startPeriodicTimer();
        $this->startStatsPeriodicTimer();
    }

    /**
     * @param string $spiderName
     * @param string $spiderClass
     *
     * @return string
     */
    public function runSpiderJob($spiderName, $spiderClass)
    {
        $spider = new $spiderClass($spiderName, $this->client, $this->adaptersRegistry);
        $job = new Job($spider);
        $jobId = $job->getId();
        $job->initLogger();
        $this->activeJobs[$jobId] = $job;
        $this->logger->info(sprintf('Job started. Spider: %s. ID: %s', $spiderName, $jobId));

        return $jobId;
    }

    /**
     * Stop the worker. Finish jobs.
     */
    public function stop()
    {
        $this->stopping = true;
        $this->logger->info('Stopping all jobs');
        if ($this->activeJobs) {
            foreach ($this->activeJobs as $job) {
                $this->logger->info(sprintf('Stopping job. ID: %s', $job->getId()));
                $this->logJobStats($job);
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
                    $this->logger->info(sprintf('Job finished. ID: %s', $job->getId()));
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
                    $this->logger->info(sprintf('Job in progress. ID: %s', $job->getId()));
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
        $this->logger->info(sprintf('Work time: %d seconds', time() - $job->getStartTime(false)));
        $this->logger->info(sprintf('Total requests: %d', $job->getSpider()->getStatistic()->getTotalRequests()));
        $this->logger->info(sprintf('Success requests: %d', $job->getSpider()->getStatistic()->getSuccessRequests()));
        $this->logger->info(sprintf('Failed requests: %d', $job->getSpider()->getStatistic()->getFailedRequests()));
        $this->logger->info(sprintf('Active requests: %d', $job->getSpider()->getStatistic()->getActiveRequests()));
        $this->logger->info(sprintf('Average requests per second: %d', $job->getSpider()->getStatistic()->getRequestsPerSecond()));
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
            throw new \InvalidArgumentException(sprintf('No job with id %s was found', $id));
        }
        $job = $this->activeJobs[$id];
        $this->logger->info(sprintf('Stopping the job. ID: %s', $job->getId()));
        $this->logJobStats($job);
        $this->finishedJobs[] = $job;
        unset($this->activeJobs[$id]);
        if (!$this->activeJobs) {
            $this->client->stop();
        }
    }
}
