<?php

define('STARTED_AT', microtime(true));
require __DIR__ . '/../vendor/autoload.php';

/** @noinspection PhpUnhandledExceptionInspection */
$container = (new \WatchNext\Engine\Dispatcher\KernelLoader())->load();

/** @noinspection PhpUnhandledExceptionInspection */
$container->get(\WatchNext\Engine\Dispatcher\HttpDispatcher::class)->dispatch();
