<?php

namespace WatchNext\Engine\Session;

use WatchNext\Engine\Config;
use WatchNext\Engine\Database\Database;

class Security {
    private static ?array $config = null;

    public function __construct(private readonly Database $database) {
        if (self::$config === null) {
            self::$config = (new Config())->get('security.php');
        }
    }

    public function init(): void {
        session_start();
        $_SESSION[CSFR::TOKEN_KEY] = $_SESSION[CSFR::TOKEN_KEY] ?? bin2hex(random_bytes(20));
        $this->tryAuthorizeFromCookie();
    }

    public function authorize($user, $rememberMe = false): void {
        $this->update($user);

        if ($rememberMe) {
            $securityConfig = self::$config;

            $key = bin2hex(random_bytes(8));
            $token = bin2hex(random_bytes(15));

            $connection = $this->database->getConnection();
            $sth = $connection->prepare("
                UPDATE `{$securityConfig['user']['table_name']}`(
                `{$securityConfig['user']['remember_me_key']}`, 
                `{$securityConfig['user']['remember_me_token']}`
                ) VALUES (:key, :token) WHERE `{$securityConfig['user']['id']}` = :id
            ");
            $sth->executeStatement(['key' => $key, 'token' => password_hash($token), 'id' => $user->{$securityConfig['user']['id']}]);

            setcookie('rmmbr.key', $key);
            setcookie('rmmbr.token', $token);
        }
    }

    public function tryAuthorizeFromCookie(): void {
        if (isset($_SESSION['main.auth.user'])) {
            return;
        }

        $key = $_COOKIE['rmmbr.key'] ?? null;

        if ($key === null) {
            return;
        }

        $securityConfig = self::$config;
        $connection = $this->database->getConnection();
        $sth = $connection->prepare("
            SELECT * 
            FROM `{$securityConfig['user']['table_name']}`
            WHERE `{$securityConfig['user']['remember_me_key']}` = :key
            LIMIT 1
        ");
        $user = $sth->executeQuery(['key' => $key])->fetchAssociative();

        if (!$user) {
            return;
        }

        $user = (object) $user;
        $token = $_COOKIE['rmmbr.token'] ?? null;
        if (password_verify($token, $user->{$securityConfig['user']['remember_me_token']})) {
            $this->update($user);
        }
    }
}