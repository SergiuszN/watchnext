<?php

namespace WatchNext\Engine\Event;

use Exception;
use WatchNext\Engine\Config;
use WatchNext\Engine\Container;
use WatchNext\Engine\Database\Database;
use WatchNext\Engine\Session\Security;

class DatabaseEventDispatcher implements EventDispatcherInterface
{
    private array $eventList = [];

    public function __construct(
        private readonly Database $database,
        private readonly Security $security,
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
        if (!isset($this->eventList[$event])) {
            $this->eventList[$event] = [];
        }

        if (!in_array($subscriber, $this->eventList[$event])) {
            $this->eventList[$event][] = $subscriber;
        }
    }

    public function unregister(string $event, string $subscriber): void
    {
        if (!isset($this->eventList[$event])) {
            return;
        }

        $key = array_search($subscriber, $this->eventList[$event]);
        if ($key !== false) {
            unset($this->eventList[$event][$key]);
        }
    }

    public function dispatch(EventInterface $event): void
    {
        $this->database->prepare('INSERT INTO command_bus (message_class, message, status, created_at, created_by) 
            VALUES (:messageClass, :message, :status, NOW(), :createdBy)
        ')->execute([
            'messageClass' => get_class($event),
            'message' => serialize($event),
            'status' => CommandBusStatusEnum::NEW->value,
            'createdBy' => $this->security->getUserId(),
        ]);
    }

    /**
     * @throws Exception
     */
    public function execute(string $eventClass, string $eventSerialized): void
    {
        if (!isset($this->eventList[$eventClass])) {
            throw new Exception("No event handler for $eventClass");
        }

        /** @var EventInterface $event */
        $event = unserialize($eventSerialized);

        foreach ($this->eventList[$eventClass] as $listenerClass) {
            /** @var EventSubscriberInterface $listener */
            $listener = $this->container->get($listenerClass);
            $listener->execute($event);
        }
    }
}
