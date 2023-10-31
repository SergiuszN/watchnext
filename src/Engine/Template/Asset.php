<?php

namespace WatchNext\Engine\Template;

use WatchNext\Engine\Cache\ApcuCache;

class Asset {
    private string $publicPath;
    private ApcuCache $cache;

    public function __construct(ApcuCache $cache) {
        $this->publicPath = ROOT_PATH . '/public';
        $this->cache = $cache;
    }

    public function path(string $path, bool $absoluteUrl = false, bool $versioning = false): string {
        $path = strstr($path, '?', true) ?: $path;
        $prefix = $absoluteUrl ? $_ENV['SITE_URL'] : '';
        $resultPath = $prefix . $path;

        if ($versioning) {
            $filePath = $this->publicPath . $path;
            $hash = $this->cache->get('asset:hash:' . $path, fn () => md5_file($filePath));
            $resultPath .= '?v=' . $hash;
        }

        return $resultPath;
    }
}