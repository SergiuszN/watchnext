<?php

namespace WatchNext\Engine;

use WatchNext\Engine\Cache\FileSystemCache;
use WatchNext\Engine\Database\Database;
use WatchNext\Engine\Request\Request;
use WatchNext\Engine\Router\DispatchedRoute;
use WatchNext\Engine\Session\Auth;
use WatchNext\WatchNext\Domain\User\User;

class DevTools {
    private bool $enabled;
    private FileSystemCache $storage;
    private ?Database $database;

    private static string $id = '';

    public function __construct() {
        if (!self::$id) {
            self::$id = bin2hex(random_bytes(5));
        }

        $this->enabled = $_ENV['APP_ENV'] === 'dev';
        $this->storage = new FileSystemCache();

        if ($this->enabled) {
            $this->database = new Database();
        }
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
            'method' => $_SERVER['REQUEST_METHOD'],
            'uri' => $_SERVER['REQUEST_URI'],
            'started' => microtime(true) * 1000000,
            'events' => [],
            'database' => [],
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

        $requests[self::$id]['max_memory'] = memory_get_peak_usage(false);
        $requests[self::$id]['ended'] = microtime(true) * 1000000;
        $requests[self::$id]['executed_in'] = $requests[self::$id]['ended'] - $requests[self::$id]['started'];

        $this->storage->set('dev.tools', $requests);
    }

    public function end(bool $render): void {
        if (!$this->enabled) {
            return;
        }

        $requests = $this->storage->read('dev.tools') ?? [];

        $requests[self::$id]['max_memory'] = memory_get_peak_usage(false);
        $requests[self::$id]['user'] = (new Auth())->getUser();
        $requests[self::$id]['route'] = (new Request())->getRoute();
        $requests[self::$id]['database'] = $this->database?->getLogs();
        $requests[self::$id]['ended'] = microtime(true) * 1000000;
        $requests[self::$id]['executed_in'] = $requests[self::$id]['ended'] - $requests[self::$id]['started'];

        $this->storage->set('dev.tools', $requests);

        if ($render) {
            $this->render();
        }
    }

    public function render(): void {
        $requests = $this->storage->read('dev.tools') ?? [];

        echo "<hr style='margin-top: 100px'>";

        foreach (array_reverse($requests) as $request) {
            $microtime = $request['executed_in'];
            $memoryMB = $request['max_memory'] / 1000000;
            $time = $microtime / 1000000;

            echo '--------------------------------------------------------------------<br>';
            echo "Request: {$request['method']} to {$request['uri']}<br>";

            if (isset($request['route'])) {
                /** @var DispatchedRoute $route */
                $route = $request['route'];
                echo "Route: {$route->routeName}<br>";
            }

            if (isset($request['user'])) {
                /** @var User $user */
                $user = $request['user'];
                echo "Logged user: {$user->getId()}<br>";
            } else {
                echo "No user logged<br>";
            }

            if (!empty($request['database'])) {
                $countOfQueries = count($request['database']);
                $timeOfQueries = round(array_sum(array_map(fn ($log) => $log['time'], $request['database'])) * 1000000);

                echo "Database queries: {$countOfQueries} in $timeOfQueries (microseconds)<br>";
            }

            echo "Executed in: {$microtime} (microseconds)  / {$time} (seconds)<br>";
            echo "Memory used: {$memoryMB} Mb<br><br>";

            foreach ($request['events'] as $event) {
                echo "Event: {$event['event']}, Executed in {$event['executed_in']} (microseconds)<br>";

                if ($event['data']) {
                    echo "Event data: <br>";
                    echo '<pre>';
                    var_dump($event['data']);
                    echo '</pre>';
                }
            }

            foreach ($request['database'] as $query) {
                echo "<br>";
                $microtime = round($query['time'] * 1000000);
                echo "Query ($microtime microseconds): <br>";
                echo '<pre>';
                var_dump($query['query']);
                echo '</pre>';
                echo "Params: <br>";
                echo '<pre>';
                var_dump($query['params']);
                echo '</pre>';
            }
        }
    }
}