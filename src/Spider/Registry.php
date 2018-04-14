<?php

namespace Hotrush\Stealer\Spider;

class Registry
{
    /**
     * @var array
     */
    private $spiders = [];

    /**
     * Register new spider's class name.
     *
     * @param $name
     * @param $classname
     */
    public function registerSpider(string $name, string $classname): void
    {
        if (isset($this->spiders[$name])) {
            throw new \InvalidArgumentException('Spider\'s name already exists.');
        }
        if (in_array($classname, $this->spiders)) {
            throw new \InvalidArgumentException('Spider\'s class already exists.');
        }

        $this->spiders[$name] = $classname;
    }

    /**
     * Retrieve all registered spiders.
     *
     * @return array
     */
    public function getSpiders(): array
    {
        return $this->spiders;
    }

    /**
     * Get spider class by name.
     *
     * @param $name
     *
     * @return string
     */
    public function getSpider(string $name): string
    {
        if (!isset($this->spiders[$name])) {
            throw new \InvalidArgumentException('No spider with name '.$name.' was found');
        }

        return $this->spiders[$name];
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function spiderExists($name): bool
    {
        return isset($this->spiders[$name]);
    }
}
