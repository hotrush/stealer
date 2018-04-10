<?php

namespace Hotrush\Stealer\Spider;

use Hotrush\Stealer\AbstractClient;
use Hotrush\Stealer\AdaptersRegistry;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;

abstract class SpiderAbstract
{
    /**
     * @var AbstractClient
     */
    private $client;

    /**
     * @var AdaptersRegistry
     */
    protected $adaptersRegistry;

    /**
     * Requests per second.
     *
     * @var int
     */
    private $perTick = 4;

    /**
     * @var Request[]
     */
    protected $requests = [];

    /**
     * @var Statistic
     */
    protected $statistic;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * SpiderAbstract constructor.
     *
     * @param AbstractClient   $client
     * @param AdaptersRegistry $adaptersRegistry
     */
    public function __construct(AbstractClient $client, AdaptersRegistry $adaptersRegistry)
    {
        $this->client = $client;
        $this->adaptersRegistry = $adaptersRegistry;
        $this->statistic = new Statistic();
        $startRequest = $this->getStartRequest();
        if ($startRequest) {
            $this->requests[] = $startRequest;
        }
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return AbstractClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return Statistic
     */
    public function getStatistic()
    {
        return $this->statistic;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return count($this->requests) || $this->statistic->getActiveRequests();
    }

    /**
     * Initial requests.
     *
     * @return Request
     */
    abstract public function getStartRequest();

    /**
     * Send requests for this tick.
     */
    public function executeTickRequests()
    {
        for ($i = 1; $i <= $this->perTick; $i++) {
            $request = array_shift($this->requests);

            if ($request) {
                $this->statistic->incrementTotalRequests();
                $this->statistic->incrementActiveRequests();
                $request->send($this->getClient());
            }
        }
    }

    /**
     * @param $reason
     */
    public function errorCallback($reason)
    {
        $this->statistic->decrementActiveRequests();
        $this->statistic->incrementFailedRequests();
        $this->logger->error('Error receiving page: '.(string) $reason);
    }

    /**
     * @param ResponseInterface $response
     *
     * @return Crawler
     */
    protected function createCrawlerFromResponse(ResponseInterface $response)
    {
        $crawler = new Crawler();
        $crawler->addHtmlContent((string) $response->getBody()->getContents());

        return $crawler;
    }
}
