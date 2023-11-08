<?php

namespace WatchNext\WatchNext\Domain\Item;

use DateTimeImmutable;
use Exception;
use WatchNext\WatchNext\Domain\Catalog\Catalog;

class Item
{
    private ?int $id = null;
    private string $title;
    private string $url;
    private string $description;
    private string $image;
    private int $owner;
    private ?Catalog $catalogModel = null;
    private int $catalog;
    private DateTimeImmutable $addedAt;
    private bool $isWatched;
    private string $note;
    /** @var ItemTag[] */
    private array $tags = [];

    public static function create(string $title, string $url, string $description, string $image, int $catalog, int $owner): Item
    {
        $item = new Item();
        $item->setAddedAt(new DateTimeImmutable());
        $item->setTitle($title);
        $item->setUrl($url);
        $item->setDescription($description);
        $item->setImage($image);
        $item->setIsWatched(false);
        $item->setNote('');
        $item->setCatalog($catalog);
        $item->setOwner($owner);

        return $item;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Item
    {
        $this->id = $id;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): Item
    {
        $this->title = $title;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): Item
    {
        $this->url = $url;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getSlugDescription(): string
    {
        return mb_strlen($this->description) > 150
            ? mb_substr($this->description, 0, 150) . '...'
            : $this->description;
    }

    public function setDescription(string $description): Item
    {
        $this->description = $description;

        return $this;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function setImage(string $image): Item
    {
        $this->image = $image;

        return $this;
    }

    public function getOwner(): int
    {
        return $this->owner;
    }

    public function setOwner(int $owner): Item
    {
        $this->owner = $owner;

        return $this;
    }

    public function getCatalog(): int
    {
        return $this->catalog;
    }

    public function setCatalog(int $catalog): Item
    {
        $this->catalog = $catalog;

        return $this;
    }

    public function getAddedAt(): DateTimeImmutable
    {
        return $this->addedAt;
    }

    public function setAddedAt(DateTimeImmutable $addedAt): Item
    {
        $this->addedAt = $addedAt;

        return $this;
    }

    public function isWatched(): bool
    {
        return $this->isWatched;
    }

    public function setIsWatched(bool $isWatched): Item
    {
        $this->isWatched = $isWatched;

        return $this;
    }

    public function toggleWatched(): self
    {
        $this->isWatched = !$this->isWatched;

        return $this;
    }

    public function getNote(): string
    {
        return $this->note;
    }

    public function setNote(string $note): Item
    {
        $this->note = $note;

        return $this;
    }

    public function setTags(array $tags): Item
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * @return ItemTag[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    public function getOtherTags(array $allTags): array
    {
        $ownedTags = array_map(fn (ItemTag $tag) => $tag->getValue(), $this->tags);

        return array_diff($allTags, $ownedTags);
    }

    public function getCatalogModel(): ?Catalog
    {
        return $this->catalogModel;
    }

    public function setCatalogModel(Catalog $catalog): Item
    {
        $this->catalogModel = $catalog;

        return $this;
    }

    /**
     * @throws Exception
     */
    public static function fromDatabase(array $item): Item
    {
        $model = new Item();
        $model->id = (int) $item['id'];
        $model->title = $item['title'];
        $model->url = $item['url'];
        $model->description = $item['description'];
        $model->image = $item['image'];
        $model->owner = $item['owner'];
        $model->catalog = $item['catalog'];
        $model->addedAt = new DateTimeImmutable($item['added_at']);
        $model->isWatched = (bool) $item['is_watched'];
        $model->note = $item['note'];

        return $model;
    }

    public function toDatabase(): array
    {
        return [
            'title' => $this->title,
            'url' => $this->url,
            'description' => $this->description,
            'image' => $this->image,
            'owner' => $this->owner,
            'catalog' => $this->catalog,
            'added_at' => $this->addedAt->format('Y-m-d H:i:s'),
            'is_watched' => $this->isWatched ? 1 : 0,
            'note' => $this->note,
        ];
    }
}
