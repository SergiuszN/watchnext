<?php

namespace WatchNext\Engine\Cli;

use WatchNext\Engine\Cache\MemcacheCache;

class CacheClearCommand implements CliCommandInterface {
    public function execute(): void {
        echo "Clearing...\n";

        $cacheFolder = realpath(__DIR__ . '/../../../var') . '/*';
        exec("rm -rf $cacheFolder");

        (new MemcacheCache())->clearAll();

        echo "Done!\n";
    }
}