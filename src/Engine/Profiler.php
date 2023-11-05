<?php

namespace WatchNext\Engine;

use WatchNext\Engine\Cache\ApcuCache;
use WatchNext\Engine\Database\Database;
use WatchNext\Engine\Request\Request;
use WatchNext\Engine\Router\DispatchedRoute;
use WatchNext\Engine\Session\Security;
use WatchNext\WatchNext\Domain\User\User;

class Profiler
{
    private bool $enabled;
    private static string $id = '';
    private static array $data = [];

    public function __construct(
        private readonly ApcuCache $storage,
        private readonly Database $database,
        private readonly Security $security,
        private readonly Request $request,
    ) {
        if (!self::$id) {
            self::$id = bin2hex(random_bytes(5));
        }

        $this->enabled = ENV === 'dev';
    }

    public function start($event, mixed $data = null): void
    {
        if (!$this->enabled) {
            return;
        }

        self::$data = [
            'id' => self::$id,
            'method' => $_SERVER['REQUEST_METHOD'],
            'uri' => $_SERVER['REQUEST_URI'],
            'started' => STARTED_AT * 1000000,
            'events' => [],
            'last_tick' => STARTED_AT * 1000000,
            'database' => [],
        ];

        $this->add($event, $data);
    }

    public function add($event, mixed $data = null): void
    {
        if (!$this->enabled) {
            return;
        }

        $tick = microtime(true) * 1000000;

        self::$data['events'][] = [
            'event' => $event,
            'tick' => $tick,
            'executed_in' => $tick - self::$data['last_tick'],
            'data' => $data,
        ];

        self::$data['last_tick'] = $tick;
        self::$data['max_memory'] = memory_get_peak_usage(false);
        self::$data['ended'] = microtime(true) * 1000000;
        self::$data['executed_in'] = self::$data['ended'] - self::$data['started'];
    }

    public function end(bool $render): void
    {
        if (!$this->enabled) {
            return;
        }

        self::$data['max_memory'] = memory_get_peak_usage(false);
        self::$data['user'] = $this->security->getUser();
        self::$data['route'] = $this->request->getRoute();
        self::$data['database'] = $this->database->getLogs();
        self::$data['ended'] = microtime(true) * 1000000;
        self::$data['executed_in'] = self::$data['ended'] - self::$data['started'];

        $requests = $this->storage->read('profiler.storage', []);
        $requests[self::$id] = self::$data;
        $this->storage->set('profiler.storage', $requests);

        if ($render) {
            $this->render();
        }
    }

    public function render(): void
    {
        $requests = $this->storage->read('profiler.storage', []);

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
                echo 'No user logged<br>';
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
                    echo 'Event data: <br>';
                    echo '<pre>';
                    var_dump($event['data']);
                    echo '</pre>';
                }
            }

            foreach ($request['database'] as $query) {
                echo '<br>';
                $microtime = round($query['time'] * 1000000);
                echo "Query ($microtime microseconds): <br>";
                echo '<pre>';
                var_dump($query['query']);
                echo '</pre>';
                echo 'Params: <br>';
                echo '<pre>';
                var_dump($query['params']);
                echo '</pre>';
            }
        }
    }
}
