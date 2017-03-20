<?php

namespace Hotrush\Stealer\Spider;

use Hotrush\Stealer\AbstractClient;

class Request
{
    /**
     * http method
     *
     * @var string
     */
    private $method;

    /**
     * uri to request
     *
     * @var string
     */
    private $uri;

    /**
     * @var callable
     */
    private $callback;

    /**
     * @var callable
     */
    private $errorCallback;

    /**
     * SpiderRequest constructor.
     *
     * @param $method
     * @param $uri
     * @param callable $callback
     * @param callable $errorCallback
     */
    public function __construct($method, $uri, callable $callback, callable $errorCallback)
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->callback = $callback;
        $this->errorCallback = $errorCallback;
    }

    /**
     * @param AbstractClient $client
     */
    public function send(AbstractClient $client)
    {
        $client->getClient()->requestAsync($this->method, $this->uri)
            ->then($this->callback)
            ->otherwise($this->errorCallback);
    }
}
