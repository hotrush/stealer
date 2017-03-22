<?php

namespace Hotrush\Stealer\Spider;

class Statistic
{
    /**
     * @var int
     */
    private $totalRequests = 0;

    /**
     * @var int
     */
    private $activeRequests = 0;

    /**
     * @var int
     */
    private $successRequests = 0;

    /**
     * @var int
     */
    private $failedRequests = 0;

    /**
     * @var int
     */
    private $startTime;

    /**
     * Statistic constructor.
     *
     * @param null $time
     */
    public function __construct($time = null)
    {
        $this->startTime = $time ?: time();
    }

    /**
     * @param int $num
     */
    public function incrementTotalRequests($num = 1)
    {
        $this->totalRequests += (int) $num;
    }

    /**
     * @return int
     */
    public function getTotalRequests()
    {
        return $this->totalRequests;
    }

    /**
     * @param int $num
     */
    public function incrementActiveRequests($num = 1)
    {
        $this->activeRequests += (int) $num;
    }

    /**
     * @param int $num
     */
    public function decrementActiveRequests($num = 1)
    {
        $this->activeRequests -= (int) $num;
    }

    /**
     * @return int
     */
    public function getActiveRequests()
    {
        return $this->activeRequests;
    }

    /**
     * @param int $num
     */
    public function incrementSuccessRequests($num = 1)
    {
        $this->successRequests += (int) $num;
    }

    /**
     * @return int
     */
    public function getSuccessRequests()
    {
        return $this->successRequests;
    }

    /**
     * @param int $num
     */
    public function incrementFailedRequests($num = 1)
    {
        $this->failedRequests += (int) $num;
    }

    /**
     * @return int
     */
    public function getFailedRequests()
    {
        return $this->failedRequests;
    }

    /**
     * @return float
     */
    public function getRequestsPerSecond()
    {
        return time() > $this->startTime ? round($this->totalRequests / (time() - $this->startTime), 2) : 0;
    }
}
