<?php

namespace WatchNext\Engine\Event;

class EventDispatcher {
    private static ?\League\Event\EventDispatcher $dispatcher = null;

    public function __construct() {
        if (self::$dispatcher !== null) {
            return;
        }

        self::$dispatcher = new \League\Event\EventDispatcher();
    }

    public function subscribeTo(string $eventIdentifier, EventListenerInterface $listener): void {
        self::$dispatcher->subscribeTo($eventIdentifier, new EventListener($listener));
    }

    public function dispatch(object $event): void {
        self::$dispatcher->dispatch($event);
    }
}