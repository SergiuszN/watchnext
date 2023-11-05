<?php

namespace WatchNext\Engine\Event;

interface EventDispatcherInterface
{
    public function register(string $event, string $subscriber): void;

    public function unregister(string $event, string $subscriber): void;

    public function dispatch(EventInterface $event): void;
}
