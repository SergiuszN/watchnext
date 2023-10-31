<?php

namespace WatchNext\Engine;

use Dotenv\Dotenv;

class Env {
    public function load(): void {
        $dotenv = Dotenv::createImmutable(ROOT_PATH);
        $dotenv->load();
    }
}