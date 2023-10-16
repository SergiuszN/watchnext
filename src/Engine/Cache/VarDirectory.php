<?php

namespace WatchNext\Engine\Cache;

use WatchNext\Engine\Config;

class VarDirectory {

    public function init(): void {
        $dir = (new Config())->getRootPath() . '/var';

        if (!file_exists($dir)) {
            mkdir($dir);
        }

        if (!file_exists($dir . '/cache')) {
            mkdir($dir . '/cache');
        }

        if (!file_exists($dir . '/log')) {
            mkdir($dir . '/log');
        }
    }
}