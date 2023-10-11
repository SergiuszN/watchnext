<?php

namespace WatchNext\Engine\Cli;

use WatchNext\Engine\Cache\ArrayCache;
use WatchNext\Engine\Cache\MemcacheCache;
use WatchNext\Engine\Cache\RedisCache;

class CacheClearCommand implements CliCommandInterface {
    public function execute(): void {
        echo "Clearing...\n";

        $cacheFolder = realpath(__DIR__ . '/../../../var') . '/*';
        exec("rm -rf $cacheFolder");

        (new ArrayCache())->clearAll();
        (new MemcacheCache())->clearAll();
        (new RedisCache())->clearAll();

        echo "Done!\n";
    }
}