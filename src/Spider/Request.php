<?php

namespace Hotrush\Stealer\Spider;

use Hotrush\Stealer\AbstractClient;

class Request
{
    /**
     * Http method.
     *
     * @var string
     */
    private $method;

    /**
     * Uri to request.
     *
     * @var string
     */
    private $uri;

    /**
     * Request options.
     *
     * @var array
     */
    private $options = [];

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
     * @param string   $method
     * @param string   $uri
     * @param array    $options
     * @param callable $callback
     * @param callable $errorCallback
     */
    public function __construct($method, $uri, array $options, callable $callback, callable $errorCallback)
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->options = $options;
        $this->callback = $callback;
        $this->errorCallback = $errorCallback;
    }

    /**
     * @param AbstractClient $client
     */
    public function send(AbstractClient $client)
    {
        $client->getClient()->requestAsync($this->method, $this->uri, $this->options)
            ->then($this->callback)
            ->otherwise($this->errorCallback);
    }
}
