<?php

namespace WatchNext\Engine\Dispatcher;

use Exception;
use WatchNext\Engine\Container;
use WatchNext\Engine\Env;

class InternalDispatcher {
    /**
     * @throws Exception
     */
    public function dispatch(): void {
        (new Env())->load();
        (new Container())->init();
    }
}