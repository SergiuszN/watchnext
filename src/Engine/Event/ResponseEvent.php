<?php

namespace WatchNext\Engine\Event;

use League\Event\HasEventName;

readonly class ResponseEvent implements HasEventName {
    public function __construct() {
    }

    public function eventName(): string {
        return 'kernel.response';
    }
}