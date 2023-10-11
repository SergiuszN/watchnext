<?php

namespace WatchNext\Engine;

use Dotenv\Dotenv;

class Env {
    public function load(): void {
        $dotenv = Dotenv::createImmutable(realpath(__DIR__ . '/../../'));
        $dotenv->load();
    }
}