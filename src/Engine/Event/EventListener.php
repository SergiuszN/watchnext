<?php

namespace WatchNext\Engine\Event;

use League\Event\Listener;

readonly class EventListener implements Listener {
    public function __construct(private EventListenerInterface $eventListener) {
    }

    public function __invoke(object $event): void {
        $this->eventListener->__invoke($event);
    }
}