<?php

namespace Hotrush\Stealer\Client;

use Hotrush\ScrapoxyClient\Client;
use Hotrush\Stealer\AbstractClient;
use Hotrush\Stealer\Middleware;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use React\Dns\Resolver\Factory;
use WyriHaximus\React\GuzzlePsr7\HttpClientAdapter;

class Scrapoxy extends AbstractClient
{
    /**
     * @var bool
     */
    private $scrapoxyScaled = false;

    /**
     * @var int
     */
    private $scalingDelay = 120;

    /**
     * @var bool
     */
    private $waiting = false;

    /**
     * @var \Hotrush\ScrapoxyClient\Client
     */
    protected $scrapoxyClient;

    protected function createClient()
    {
        $dnsResolverFactory = new Factory();
        $dnsResolver = $dnsResolverFactory->createCached('8.8.8.8', $this->loop);

        $handler = new HttpClientAdapter($this->loop, null, $dnsResolver);
        $stack = HandlerStack::create($handler);
        $stack->push(Middleware::proxy(getenv('SCRAPOXY_PROXY')));
        $stack->push(Middleware::userAgent());
        $this->client = new GuzzleClient([
            'handler' => $stack,
        ]);
    }

    protected function getScrapoxtClient()
    {
        if (!$this->scrapoxyClient) {
            $this->scrapoxyClient = new Client(getenv('API_SCRAPOXY'), getenv('API_SCRAPOXY_PASSWORD'), $this->loop);
        }

        return $this->scrapoxyClient;
    }

    public function start()
    {
        parent::start();

        if ($this->waiting) {
            return;
        }

        if (!$this->scrapoxyScaled) {
            $this->logger->info('Scaling scrapoxy');
            $this->waiting = true;
            $this->getScrapoxtClient()->upScale()
                ->then(
                    function () {
                        $this->logger->info('Waiting for scaling: ' . $this->scalingDelay.'sec');
                        $this->loop->addTimer($this->scalingDelay, function() {
                            $this->scrapoxyScaled = true;
                            $this->waiting = false;
                        });
                    },
                    function ($reason) {
                        // @todo throw an error and stop spider
                        $this->logger->error('Error while scaling: ' . ((string) $reason));
                        $this->waiting = false;
                        $this->scrapoxyScaled = false;
                    }
                );
        }
    }

    public function end()
    {
        parent::end();

        if ($this->waiting) {
            return;
        }

        if ($this->scrapoxyScaled) {
            $this->logger->info('Downscaling scrapoxy');
            $this->waiting = true;
            $this->getScrapoxtClient()->downScale()
                ->then(
                    function () {
                        $this->logger->info('Waiting for scaling: '.$this->scalingDelay.'sec');
                        $this->loop->addTimer($this->scalingDelay, function() {
                            $this->scrapoxyScaled = true;
                            $this->waiting = false;
                        });
                    },
                    function ($reason) {
                        $this->logger->error('Error while scaling down: ' . ((string) $reason));
                        // @todo throw an error and stop spider
                        $this->waiting = false;
                        $this->scrapoxyScaled = false;
                    }
                );
        }
    }

    public function isReady()
    {
        return $this->scrapoxyScaled;
    }

    public function isStopped()
    {
        return !$this->waiting && !$this->scrapoxyScaled;
    }
}
