<?php

namespace WatchNext\WatchNext\Domain\Item;

use Exception;

class ItemCurlBuilder
{
    private string $page;
    private Item $item;
    private int $catalog;
    private int $owner;

    public function __construct(private string $url)
    {
    }

    public function load(): self
    {
        $ch = curl_init($this->url);
        curl_setopt_array($ch, [
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:60.0) Gecko/20100101 Firefox/60.0',
            CURLOPT_ENCODING => 'gzip, deflate',
            CURLOPT_HTTPHEADER => [
                'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language: en-US,en;q=0.5',
                'Accept-Encoding: gzip, deflate',
                'Connection: keep-alive',
                'Upgrade-Insecure-Requests: 1',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        $this->page = curl_exec($ch);

        return $this;
    }

    public function setCatalog(int $catalog): self
    {
        $this->catalog = $catalog;

        return $this;
    }

    public function setOwner(int $owner): self
    {
        $this->owner = $owner;

        return $this;
    }

    public function parse(): self
    {
        [$matches, $descriptionMatch] = [[], []];
        preg_match_all('~<\s*meta\s+property="(og:[^"]+)"\s+content="([^"]*)~i', $this->page, $matches);
        preg_match('<meta\s*name="description"\s*content="([^"]*)">', $this->page, $descriptionMatch);

        $title = $matches[2][array_search('og:title', $matches[1])] ?? '';
        $url = $this->url;
        $descriptionOg = $matches[2][array_search('og:description', $matches[1])] ?? '';
        $descriptionMeta = $descriptionMatch[1] ?? '';
        $description = mb_strlen($descriptionMeta) > mb_strlen($descriptionOg) ? $descriptionMeta : $descriptionOg;
        $image = $matches[2][array_search('og:image', $matches[1])] ?? '';

        if ($title === '' || $description === '' || $image === '') {
            throw new Exception('Cant parse url :(. Please contact with as for adding selected site');
        }

        if (!str_starts_with($image, 'http')) {
            $prefix = substr($url, 0, strpos($url, '/', strpos($url, '//') + 2));
            $image = $prefix . $image;
        }

        $this->item = Item::create($title, $url, $description, $image, $this->catalog, $this->owner);

        return $this;
    }

    public function getItem(): Item
    {
        return $this->item;
    }
}
