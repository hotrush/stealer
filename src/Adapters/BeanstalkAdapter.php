<?php

namespace Hotrush\Stealer\Adapters;

use Pheanstalk\Pheanstalk;
use Hotrush\Stealer\AdapterInterface;

class BeanstalkAdapter implements AdapterInterface
{
    private $adapter;

    public function __construct()
    {
        $this->adapter = (new Pheanstalk(getenv('BEANSTALK_HOST'), getenv('BEANSTALK_PORT')))
            ->useTube(getenv('BEANSTALK_TUBE'));
    }

    public function getAdapter()
    {
        return $this->adapter;
    }
}