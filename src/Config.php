<?php

namespace Hotrush\Stealer;

use Hotrush\Stealer\Spider\Registry;
use josegonzalez\Dotenv\Loader;
use React\EventLoop\LoopInterface;

class Config
{
    /**
     * @param string $configPath
     */
    public static function load(string $configPath): void
    {
        (new Loader($configPath))
            ->parse()
            ->putenv();
    }

    /**
     * @param string $logDir
     */
    public static function setLogsDir(string $logDir): void
    {
        putenv('LOG_DIR='.$logDir);
    }

    /**
     * @param string        $filePath
     * @param LoopInterface $loop
     *
     * @return AdaptersRegistry
     */
    public static function loadAdapters(string $filePath, LoopInterface $loop): AdaptersRegistry
    {
        $adapters = include $filePath;
        $adaptersRegistry = new AdaptersRegistry();
        if ($adapters) {
            foreach ($adapters as $name => $adapter) {
                $adaptersRegistry->addAdapter($name, new $adapter($loop));
            }
        }

        return $adaptersRegistry;
    }

    /**
     * @param string $filePath
     *
     * @return Registry
     */
    public static function loadRegistry(string $filePath): Registry
    {
        $spiders = include $filePath;
        $registry = new Registry();
        if ($spiders) {
            foreach ($spiders as $name => $spider) {
                $registry->registerSpider($name, $spider);
            }
        }

        return $registry;
    }

    /**
     * Get parsed env value.
     *
     * @param $key
     * @param null $default
     *
     * @return mixed
     */
    public static function getenv(string $key, $default = null)
    {
        $value = getenv($key);

        if ($value === false) {
            return $default;
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }

        if (strlen($value) > 1 && $value[0] === '"' && $value[strlen($value) - 1] == '"') {
            return substr($value, 1, -1);
        }

        return $value;
    }
}
