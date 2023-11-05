<?php

return [
    \WatchNext\Engine\Event\EventDispatcherInterface::class => \DI\autowire(\WatchNext\Engine\Event\SyncEventDispatcher::class),
];
