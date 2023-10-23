<?php

namespace WatchNext\WatchNext\Domain\Catalog;

class Catalog {
    private ?int $id = null;
    private int $owner;
    private bool $shared;
    private bool $default;
    private string $name;
}