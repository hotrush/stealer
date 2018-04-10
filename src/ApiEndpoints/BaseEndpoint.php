<?php

namespace Hotrush\Stealer\ApiEndpoints;

use Hotrush\Stealer\Worker;
use Hotrush\Stealer\Spider\Registry;

class BaseEndpoint
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Worker
     */
    protected $worker;

    /**
     * BaseEndpoint constructor.
     *
     * @param Registry  $registry
     * @param Worker    $worker
     */
    public function __construct(Registry $registry, Worker $worker)
    {
        $this->registry = $registry;
        $this->worker = $worker;
    }
}