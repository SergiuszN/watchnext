<?php

namespace WatchNext\Engine\Session;

use WatchNext\Engine\Config;

class Security {

    private static ?array $config = null;

    public function __construct() {
        if (self::$config === null) {
            self::$config = (new Config())->get('security.php');
        }
    }

    public function init(): void {
        session_start();
        $_SESSION[CSFR::TOKEN_KEY] = $_SESSION[CSFR::TOKEN_KEY] ?? bin2hex(random_bytes(20));
    }

    public function getConfig(): array {
        return self::$config;
    }
}