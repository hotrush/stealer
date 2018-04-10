<?php

namespace Hotrush\Stealer\Adapters;

use Pheanstalk\Pheanstalk;
use Hotrush\Stealer\AdapterInterface;

class BeanstalkAdapter implements AdapterInterface
{
    /**
     * @var Pheanstalk
     */
    private $adapter;

    /**
     * BeanstalkAdapter constructor.
     *
     * @todo not async...
     */
    public function __construct()
    {
        $this->adapter = (new Pheanstalk(getenv('BEANSTALK_HOST'), getenv('BEANSTALK_PORT')))
            ->useTube(getenv('BEANSTALK_TUBE'));
    }

    /**
     * @return Pheanstalk
     */
    public function getAdapter()
    {
        return $this->adapter;
    }
}
