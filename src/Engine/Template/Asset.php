<?php

namespace WatchNext\Engine\Template;

use WatchNext\Engine\Cache\CacheInterface;
use WatchNext\Engine\Config;

class Asset {
    private string $publicPath;
    private CacheInterface $cache;

    public function __construct(CacheInterface $cache, Config $config) {
        $this->publicPath = $config->getRootPath() . '/public';
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