<?php

namespace WatchNext\Engine\Dispatcher;

use Exception;
use WatchNext\Engine\Container;
use WatchNext\Engine\Env;
use WatchNext\Engine\Event\KernelEventRegistration;
use WatchNext\Engine\VarDirectory;

class InternalDispatcher {
    /**
     * @throws Exception
     */
    public function dispatch(): void {
        (new VarDirectory())->init();
        (new Env())->load();
        (new Container())->init();
    }
}