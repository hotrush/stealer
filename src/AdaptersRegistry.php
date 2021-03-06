<?php

namespace Hotrush\Stealer;

class AdaptersRegistry
{
    /**
     * @var AdapterInterface[]
     */
    private $adapters = [];

    /**
     * @param string $adapterName
     *
     * @return mixed
     */
    public function getAdapter(string $adapterName)
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
    public function addAdapter(string $adapterName, AdapterInterface $instance): void
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
