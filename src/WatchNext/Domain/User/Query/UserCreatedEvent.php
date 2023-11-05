<?php

namespace WatchNext\WatchNext\Domain\User\Query;

use WatchNext\Engine\Event\EventInterface;

readonly class UserCreatedEvent implements EventInterface
{
    public function __construct(
        public int $userId,
    ) {
    }
}
