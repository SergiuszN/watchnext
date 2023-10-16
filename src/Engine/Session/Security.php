<?php

namespace WatchNext\Engine\Session;

class Security {

    public function init(): void {
        session_start();
        $_SESSION[CSFR::TOKEN_KEY] = $_SESSION[CSFR::TOKEN_KEY] ?? bin2hex(random_bytes(20));
    }
}