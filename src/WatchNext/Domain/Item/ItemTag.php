<?php

namespace WatchNext\WatchNext\Domain\Item;

use DateTimeImmutable;
use Exception;

class ItemTag
{
    private int $id;
    private int $item;
    private string $value;
    private DateTimeImmutable $createdAt;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): ItemTag
    {
        $this->id = $id;
        return $this;
    }

    public function getItem(): int
    {
        return $this->item;
    }

    public function setItem(int $item): ItemTag
    {
        $this->item = $item;
        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): ItemTag
    {
        $this->value = $value;
        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): ItemTag
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @throws Exception
     */
    public static function fromDatabase(array $itemTag): ItemTag
    {
        $model = new ItemTag();
        $model->id = (int) $itemTag['id'];
        $model->item = (int) $itemTag['item'];
        $model->value = $itemTag['value'];
        $model->createdAt = new DateTimeImmutable($itemTag['created_at']);

        return $model;
    }

    public function toDatabase(): array
    {
        return [
            'item' => $this->item,
            'value' => $this->value,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }
}
