<?php

namespace Hotrush\Stealer;

use josegonzalez\Dotenv\Loader;
use React\EventLoop\LoopInterface;
use Hotrush\Stealer\Spider\Registry;

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
}
