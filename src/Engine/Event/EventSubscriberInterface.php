<?php

namespace WatchNext\Engine\Event;

interface EventSubscriberInterface
{
    public function execute(EventInterface $event): void;
}
