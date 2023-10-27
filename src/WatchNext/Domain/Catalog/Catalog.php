<?php

namespace WatchNext\WatchNext\Domain\Catalog;

use DateTimeImmutable;
use Exception;
use WatchNext\WatchNext\Domain\Item\Item;

class Catalog {
    private ?int $id = null;
    private int $owner;
    private bool $shared;
    private bool $default;
    private string $name;
    private DateTimeImmutable $createdAt;

    /** @var array|Item[] */
    private array $items = [];

    public function getId(): ?int {
        return $this->id;
    }

    public function setId(?int $id): Catalog {
        $this->id = $id;
        return $this;
    }

    public function getOwner(): int {
        return $this->owner;
    }

    public function setOwner(int $owner): Catalog {
        $this->owner = $owner;
        return $this;
    }

    public function isShared(): bool {
        return $this->shared;
    }

    public function setShared(bool $shared): Catalog {
        $this->shared = $shared;
        return $this;
    }

    public function isDefault(): bool {
        return $this->default;
    }

    public function setDefault(bool $default): Catalog {
        $this->default = $default;
        return $this;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): Catalog {
        $this->name = $name;
        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): Catalog {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @throws Exception
     */
    public static function fromDatabase(array $item): Catalog {
        $model = new Catalog();
        $model->id = (int) $item['id'];
        $model->owner = (int) $item['owner'];
        $model->shared = (bool) $item['shared'];
        $model->default = (bool) $item['default'];
        $model->name = $item['name'];
        $model->createdAt = new DateTimeImmutable($item['created_at']);

        return $model;
    }

    public function toDatabase(): array {
        return [
            'owner' => $this->owner,
            'shared' => $this->shared,
            'default' => $this->default,
            'name' => $this->name,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }
}