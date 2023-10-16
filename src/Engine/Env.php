<?php

namespace WatchNext\Engine;

use Dotenv\Dotenv;

class Env {
    public function load(): void {
        $dotenv = Dotenv::createImmutable((new Config())->getRootPath());
        $dotenv->load();
    }
}