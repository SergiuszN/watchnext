<?php

namespace WatchNext\Engine\Event;

interface CommandInterface
{
    public function execute(QueryInterface $query): void;
}
