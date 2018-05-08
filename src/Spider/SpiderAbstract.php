<?php

namespace Hotrush\Stealer\Spider;

use Hotrush\Stealer\AbstractClient;
use Hotrush\Stealer\AdaptersRegistry;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;
use Symfony\Component\DomCrawler\Crawler;

abstract class SpiderAbstract
{
    /**
     * @var string
     */
    private $name;

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
     * @param string           $name
     * @param AbstractClient   $client
     * @param AdaptersRegistry $adaptersRegistry
     */
    public function __construct(string $name, AbstractClient $client, AdaptersRegistry $adaptersRegistry)
    {
        $this->name = $name;
        $this->client = $client;
        $this->adaptersRegistry = $adaptersRegistry;
        $this->statistic = new Statistic();
        $startRequest = $this->getStartRequest();
        if ($startRequest) {
            $this->requests[] = $startRequest;
        }
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @return AbstractClient
     */
    public function getClient(): AbstractClient
    {
        return $this->client;
    }

    /**
     * @return Statistic
     */
    public function getStatistic(): Statistic
    {
        return $this->statistic;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return count($this->requests) || $this->statistic->getActiveRequests();
    }

    /**
     * Initial requests.
     *
     * @return Request
     */
    abstract public function getStartRequest(): Request;

    /**
     * Send requests for this tick.
     */
    public function executeTickRequests(): void
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
    public function errorCallback($reason): void
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
    protected function createCrawlerFromResponse(ResponseInterface $response): Crawler
    {
        $crawler = new Crawler();
        $crawler->addHtmlContent((string) $response->getBody()->getContents());

        return $crawler;
    }

    /**
     * Execute some code on spider start.
     *
     * @param LoopInterface $loop
     */
    public function onStart(LoopInterface $loop): void
    {
        // ...
    }

    /**
     * Execute some code on spider stop.
     *
     * @param LoopInterface $loop
     * @param bool          $finished
     *
     * @return PromiseInterface|void
     */
    public function onStop(LoopInterface $loop, bool $finished = true)
    {
        // ...
    }
}
