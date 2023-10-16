<?php

namespace WatchNext\Engine\Cli;

use WatchNext\Engine\Cache\MemcachedCache;
use WatchNext\Engine\Cli\IO\CliOutput;
use WatchNext\Engine\Config;

class CacheClearCommand implements CliCommandInterface {
    public function execute(): void {
        $output = new CliOutput();

        $output->write('Clearing the filesystem cache...');
        $cacheFolder = (new Config())->getCachePath() . '/*';
        exec("rm -rf $cacheFolder");
        $output->writeln(' OK');

        $output->write('Clearing the memcache cache...');
        (new MemcachedCache())->clearAll();
        $output->writeln(' OK');

        $output->writeln('Done!');
    }
}