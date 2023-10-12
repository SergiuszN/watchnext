<?php

namespace WatchNext\Engine\Dispatcher;

use Exception;
use JetBrains\PhpStorm\NoReturn;
use WatchNext\Engine\Response\JsonResponse;
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
            $this->render($response);

            die();
        } else {
            throw new \HttpException('Not found', 404);
        }
    }

    private function render($response): void {
        $responseClass = get_class($response);

        switch ($responseClass) {
            case TemplateResponse::class:
                /** @var $response TemplateResponse */
                echo (new TemplateEngine())->render($response);

                break;
            case JsonResponse::class:
                /** @var $response JsonResponse */
                http_response_code($response->httpCode);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode($response->data);

                break;
            default:
                echo 'Controller return unknown type of response';

                break;
        }
    }
}