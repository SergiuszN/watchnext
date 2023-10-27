<?php

namespace WatchNext\Engine\Template;

readonly class PaginationCollection {
    public function __construct(
        public int $page,
        public int $limit,
        public int $count,
        public array $items
    ) {
    }
}