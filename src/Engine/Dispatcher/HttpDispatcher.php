<?php

namespace WatchNext\Engine\Dispatcher;

use Exception;
use Throwable;
use WatchNext\Engine\Container;
use WatchNext\Engine\Event\EventDispatcher;
use WatchNext\Engine\Event\ExceptionEvent;
use WatchNext\Engine\Event\KernelEventRegistration;
use WatchNext\Engine\Event\RequestEvent;
use WatchNext\Engine\Event\ResponseEvent;
use WatchNext\Engine\Logger;
use WatchNext\Engine\Response\JsonResponse;
use WatchNext\Engine\Response\TemplateResponse;
use WatchNext\Engine\Router\RouterDispatcher;
use WatchNext\Engine\Router\RouterDispatcherStatusEnum;
use WatchNext\Engine\Session\Security;
use WatchNext\Engine\Template\TemplateEngine;

class HttpDispatcher {
    /**
     * @throws Exception|Throwable
     */
    public function dispatch(): void {
        $tick = microtime(true);

        $container = new Container();

        (new InternalDispatcher())->dispatch();
        (new KernelEventRegistration())->register();
        (new Security())->init();

        $eventDispatcher = new EventDispatcher();

        $route = $container->get(RouterDispatcher::class)->dispatch();
        $eventDispatcher->dispatch(new RequestEvent());

        if ($route->status === RouterDispatcherStatusEnum::FOUND) {

            try {
                $controller = (new Container())->get($route->class);
                $response = $controller->{$route->action}(...$route->vars);
                $eventDispatcher->dispatch(new ResponseEvent());

                $this->render($response);

                var_dump((microtime(true) - $tick) * 1000);
            } catch (Throwable $throwable) {
                (new Logger())->error($throwable);

                $eventDispatcher->dispatch(new ExceptionEvent($throwable));
                throw $throwable;
            }

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