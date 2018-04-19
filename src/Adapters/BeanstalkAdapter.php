<?php

namespace Hotrush\Stealer\Adapters;

use Hotrush\Stealer\AdapterInterface;
use Hotrush\Stealer\Config;
use Pheanstalk\Pheanstalk;

class BeanstalkAdapter implements AdapterInterface
{
    /**
     * @var Pheanstalk
     */
    private $adapter;

    /**
     * BeanstalkAdapter constructor.
     *
     * It's not async...
     */
    public function __construct()
    {
        $this->adapter = (new Pheanstalk(Config::getenv('BEANSTALK_HOST'), Config::getenv('BEANSTALK_PORT')))
            ->useTube(Config::getenv('BEANSTALK_TUBE'));
    }

    /**
     * @return Pheanstalk
     */
    public function getAdapter()
    {
        return $this->adapter;
    }
}
