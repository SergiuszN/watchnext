<?php

namespace WatchNext\WatchNext\Domain\User\Query;

use WatchNext\Engine\Event\QueryInterface;

readonly class UserCreatedQuery implements QueryInterface {
    public function __construct(
        public int $userId,
    ) {
    }
}