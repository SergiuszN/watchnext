<?php

namespace WatchNext\Engine\Event;

interface EventListenerInterface {
    public function __invoke(object $event): void;
}