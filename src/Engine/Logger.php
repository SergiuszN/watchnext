<?php

namespace WatchNext\Engine;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Psr\Log\LoggerInterface;

class Logger implements LoggerInterface {
    private static ?\Monolog\Logger $logger = null;

    public function __construct() {
        if (self::$logger !== null) {
            return;
        }

        $env = $_ENV['APP_ENV'];
        $logLevel = $env === 'prod' ? Level::Error : Level::Debug;

        $config = new Config();
        self::$logger = new \Monolog\Logger('main');
        self::$logger->pushHandler(new StreamHandler("{$config->getLogPath()}/{$env}.log", $logLevel));
    }

    public function emergency(\Stringable|string $message, array $context = []): void {
        self::$logger->emergency($message, $context);
    }

    public function alert(\Stringable|string $message, array $context = []): void {
        self::$logger->alert($message, $context);
    }

    public function critical(\Stringable|string $message, array $context = []): void {
        self::$logger->critical($message, $context);
    }

    public function error(\Stringable|string $message, array $context = []): void {
        self::$logger->error($message, $context);
    }

    public function warning(\Stringable|string $message, array $context = []): void {
        self::$logger->warning($message, $context);
    }

    public function notice(\Stringable|string $message, array $context = []): void {
        self::$logger->notice($message, $context);
    }

    public function info(\Stringable|string $message, array $context = []): void {
        self::$logger->info($message, $context);
    }

    public function debug(\Stringable|string $message, array $context = []): void {
        self::$logger->debug($message, $context);
    }

    public function log($level, \Stringable|string $message, array $context = []): void {
        self::$logger->log($message, $context);
    }
}