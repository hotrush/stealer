<?php

namespace Hotrush\Stealer;

class AdaptersRegistry
{
    /**
     * @var array
     */
    private $adapters = [];

    /**
     * @param $adapterName
     * @return mixed
     */
    public function getAdapter($adapterName)
    {
        if (!isset($this->adapters[$adapterName])) {
            throw new \InvalidArgumentException('Adapter name not exists.');
        }

        return $this->adapters[$adapterName]->getAdapter();
    }

    /**
     * @param string           $adapterName
     * @param AdapterInterface $instance
     */
    public function addAdapter($adapterName, AdapterInterface $instance)
    {
        if (isset($this->adapters[$adapterName])) {
            throw new \InvalidArgumentException('Adapter name already exists.');
        }
        if (in_array($instance, $this->adapters)) {
            throw new \InvalidArgumentException('Adapter instance already exists.');
        }

        $this->adapters[$adapterName] = $instance;
    }
}