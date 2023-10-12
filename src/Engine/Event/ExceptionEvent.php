<?php

namespace WatchNext\Engine\Event;

use League\Event\HasEventName;
use Throwable;

readonly class ExceptionEvent implements HasEventName {
    public function __construct(public Throwable $throwable) {
    }

    public function eventName(): string {
        return 'kernel.exception';
    }
}