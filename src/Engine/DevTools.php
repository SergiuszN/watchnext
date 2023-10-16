<?php

namespace WatchNext\Engine;

use WatchNext\Engine\Cache\FileSystemCache;

class DevTools {
    private bool $enabled;
    private FileSystemCache $storage;

    public function __construct() {
        $this->enabled = $_ENV['APP_ENV'] === 'dev';
        $this->storage = new FileSystemCache();
    }

    public function tick($event): void {

    }
}