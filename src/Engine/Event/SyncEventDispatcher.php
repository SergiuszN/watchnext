<?php

namespace WatchNext\Engine\Event;

use WatchNext\Engine\Config;
use WatchNext\Engine\Container;

class SyncEventDispatcher implements EventDispatcherInterface
{
    private array $queries = [];

    public function __construct(
        private readonly Container $container,
        Config $config,
    ) {
        $events = $config->get('events.php');

        foreach ($events as $query => $command) {
            $this->register($query, $command);
        }
    }

    public function register(string $event, string $subscriber): void
    {
        if (!isset($this->queries[$event])) {
            $this->queries[$event] = [];
        }

        if (!in_array($subscriber, $this->queries[$event])) {
            $this->queries[$event][] = $subscriber;
        }
    }

    public function unregister(string $event, string $subscriber): void
    {
        if (!isset($this->queries[$event])) {
            return;
        }

        $key = array_search($subscriber, $this->queries[$event]);
        if ($key !== false) {
            unset($this->queries[$event][$key]);
        }
    }

    public function dispatch(EventInterface $event): void
    {
        $class = get_class($event);

        if (!isset($this->queries[$class])) {
            return;
        }

        foreach ($this->queries[$class] as $commandClass) {
            /** @var EventSubscriberInterface $command */
            $command = $this->container->get($commandClass);
            $command->execute($event);
        }
    }
}
