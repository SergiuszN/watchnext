<?php

return [
    \WatchNext\Engine\Cache\CacheInterface::class => fn () => new \WatchNext\Engine\Cache\MemcachedCache(),
    'WatchNext\WatchNext\Application\Controller\*' => DI\autowire(),
];