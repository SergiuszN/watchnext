<?php

namespace WatchNext\Engine\Event;

use League\Event\HasEventName;

readonly class RequestEvent implements HasEventName {
    public function __construct() {
    }

    public function eventName(): string {
        return 'kernel.request';
    }
}