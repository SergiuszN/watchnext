<?php

namespace WatchNext\Engine;

use WatchNext\Engine\Cache\FileSystemCache;

class DevTools {
    private bool $enabled;
    private FileSystemCache $storage;

    private static string $id = '';

    public function __construct() {
        if (!self::$id) {
            self::$id = bin2hex(random_bytes(5));
        }

        $this->enabled = $_ENV['APP_ENV'] === 'dev';
        $this->storage = new FileSystemCache();
    }

    public function start(): void {
        if (!$this->enabled) {
            return;
        }

        $requests = $this->storage->read('dev.tools') ?? [];
        if (count($requests) > 9) {
            $requests = array_slice($requests, -9);
        }

        $requests[self::$id] = [
            'id' => self::$id,
            'started' => microtime(true) * 1000000,
            'events' => [],
        ];

        $this->storage->set('dev.tools', $requests);
    }

    public function add($event, mixed $data = null): void {
        if (!$this->enabled) {
            return;
        }

        $requests = $this->storage->read('dev.tools') ?? [];
        $lastTick = empty($requests[self::$id]['events'])
            ? $requests[self::$id]['started']
            : $requests[self::$id]['events'][array_key_last($requests[self::$id]['events'])]['tick'];
        $tick = microtime(true) * 1000000;

        $requests[self::$id]['events'][] = [
            'event' => $event,
            'tick' => $tick,
            'executed_in' => $tick - $lastTick,
            'data' => $data,
        ];

        $this->storage->set('dev.tools', $requests);
    }

    public function end(bool $render): void {
        if (!$this->enabled) {
            return;
        }

        $requests = $this->storage->read('dev.tools') ?? [];

        $requests[self::$id]['ended'] = microtime(true) * 1000000;
        $requests[self::$id]['executed_in'] = $requests[self::$id]['ended'] - $requests[self::$id]['started'];

        $this->storage->set('dev.tools', $requests);

        if ($render) {
            $this->render();
        }
    }

    public function render(): void {
        $requests = $this->storage->read('dev.tools') ?? [];

        $microtime = $requests[self::$id]['executed_in'];
        $time = $microtime / 1000000;

        echo "<hr>";
        echo "Request: {$_SERVER['REQUEST_METHOD']} to {$_SERVER['REQUEST_URI']}<br>";
        echo "Executed in: {$microtime} (microseconds)  / {$time} (seconds)<br><br>";

        foreach ($requests[self::$id]['events'] as $event) {
            echo "Event: {$event['event']}, Executed in {$event['executed_in']} (microseconds)<br>";

            if ($event['data']) {
                echo "Event data: <br>";
                echo '<pre>';
                var_dump($event['data']);
                echo '</pre>';
            }
        }
    }
}