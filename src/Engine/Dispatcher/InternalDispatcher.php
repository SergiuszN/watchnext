<?php

namespace WatchNext\Engine\Dispatcher;

use Exception;
use WatchNext\Engine\Container;
use WatchNext\Engine\Database;
use WatchNext\Engine\Env;
use WatchNext\Engine\TemplateEngine;

class InternalDispatcher {
    /**
     * @throws Exception
     */
    public function dispatch(): void {
        (new Env())->load();
        Container::create();
        Database::init();
        TemplateEngine::init();
    }
}