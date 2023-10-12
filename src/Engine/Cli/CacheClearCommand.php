<?php

namespace WatchNext\Engine\Cli;

use WatchNext\Engine\Cache\MemcacheCache;
use WatchNext\Engine\Cli\IO\CliOutput;

class CacheClearCommand implements CliCommandInterface {
    public function execute(): void {
        $output = new CliOutput();

        $output->write('Clearing the filesystem cache...');
        $cacheFolder = realpath(__DIR__ . '/../../../var') . '/*';
        exec("rm -rf $cacheFolder");
        $output->writeln(' OK');

        $output->write('Clearing the memcache cache...');
        (new MemcacheCache())->clearAll();
        $output->writeln(' OK');

        $output->writeln('Done!');
    }
}