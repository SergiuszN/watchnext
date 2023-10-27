<?php

namespace WatchNext\Engine\Event;

use WatchNext\Engine\Cache\CacheInterface;
use WatchNext\Engine\Config;
use WatchNext\Engine\Container;

class EventManager {
    private static array $queries = [];

    public function __construct(private readonly Container $container) {

    }

    public function init(Config $config): void {
        $cache = null;
        if ($_ENV['APP_ENV'] === 'prod') {
            $cache = $this->container->get(CacheInterface::class);
            if ($cache->has('kernel.event.manager')) {
                self::$queries = $cache->read('kernel.event.manager');
                return;
            }
        }

        $events = $config->get('events.php');

        foreach ($events as $query => $command) {
            $this->register($query, $command);
        }

        if ($cache) {
            $cache->set('kernel.event.manager', self::$queries);
        }
    }

    public function register(string $query, string $command): void {
        if (!isset(self::$queries[$query])) {
            self::$queries[$query] = [];
        }

        if (!in_array($command, self::$queries[$query])) {
            self::$queries[$query][] = $command;
        }
    }

    public function unregister(string $query, string $command): void {
        if (!isset(self::$queries[$query])) {
            return;
        }

        $key = array_search($command, self::$queries[$query]);
        if ($key !== false) {
            unset(self::$queries[$query][$key]);
        }
    }

    public function dispatch(QueryInterface $query): void {
        $class = get_class($query);

        if (!isset(self::$queries[$class])) {
            return;
        }

        foreach (self::$queries[$class] as $commandClass) {
            /** @var CommandInterface $command */
            $command = $this->container->get($commandClass);
            $command->execute($query);
        }
    }
}