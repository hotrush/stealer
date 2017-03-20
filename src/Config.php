<?php

namespace Hotrush\Stealer;

use josegonzalez\Dotenv\Loader;

class Config
{
    /**
     * @param $configPath
     */
    public static function load($configPath)
    {
        (new Loader($configPath))
            ->parse()
            ->putenv();
    }
}
