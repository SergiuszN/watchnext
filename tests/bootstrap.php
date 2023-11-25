<?php

require __DIR__ . '/../vendor/autoload.php';

/** @noinspection PhpUnhandledExceptionInspection */
$container = (new \WatchNext\Engine\Dispatcher\KernelLoader())->load('test');

require_once __DIR__ . '/Integration/IntegrationBootstrap.php';
(new \Integration\IntegrationBootstrap($container))();
