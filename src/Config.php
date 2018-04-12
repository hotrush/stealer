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
    public static function load($configPath)
    {
        (new Loader($configPath))
            ->parse()
            ->putenv();
    }

    /**
     * @param string $logDir
     */
    public static function setLogsDir($logDir)
    {
        putenv('LOG_DIR='.$logDir);
    }

    /**
     * @param string        $filePath
     * @param LoopInterface $loop
     *
     * @return AdaptersRegistry
     */
    public static function loadAdapters($filePath, LoopInterface $loop)
    {
        $adapters = include $filePath;
        $pipeline = new AdaptersRegistry();
        if ($adapters) {
            foreach ($adapters as $name => $adapter) {
                $pipeline->addAdapter($name, new $adapter($loop));
            }
        }

        return $pipeline;
    }

    /**
     * @param string $filePath
     *
     * @return Registry
     */
    public static function loadRegistry($filePath)
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
     * Get parsed env value
     *
     * @param $key
     * @param null $default
     * @return mixed
     */
    public static function getenv($key, $default = null)
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
                return;
        }

        if (strlen($value) > 1 && $value[0] === '"' && $value[strlen($value)-1] == '"') {
            return substr($value, 1, -1);
        }

        return $value;
    }
}
