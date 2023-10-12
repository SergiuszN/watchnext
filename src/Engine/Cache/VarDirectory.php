<?php

namespace WatchNext\Engine\Cache;

class VarDirectory {

    public function init(): void {
        $dir = __DIR__ . '/../../../var';

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