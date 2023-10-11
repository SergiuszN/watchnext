<?php

namespace WatchNext\Engine\Dispatcher;

use Exception;
use WatchNext\Engine\Response\TemplateResponse;
use WatchNext\Engine\Router\RouterDispatcher;
use WatchNext\Engine\Router\RouterDispatcherStatusEnum;
use WatchNext\Engine\TemplateEngine;

class HttpDispatcher {
    /**
     * @throws Exception
     */
    public function dispatch(): void {
        (new InternalDispatcher())->dispatch();

        $route = (new RouterDispatcher())->dispatch();

        if ($route->status === RouterDispatcherStatusEnum::FOUND) {
            $response = (new $route->class())->{$route->action}();
            $responseClass = get_class($response);

            switch ($responseClass) {
                case TemplateResponse::class:
                    echo (new TemplateEngine())->render($response);
                    die();
                default:
                    echo 'Controller return unknown type of response';
                    die();
            }

        } else {
            throw new \HttpException('Not found', 404);
        }
    }
}