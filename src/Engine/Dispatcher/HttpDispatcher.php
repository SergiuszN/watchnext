<?php

namespace WatchNext\Engine\Dispatcher;

use Exception;
use Throwable;
use WatchNext\Engine\Cache\VarDirectory;
use WatchNext\Engine\Container;
use WatchNext\Engine\DevTools;
use WatchNext\Engine\Env;
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
        (new Env())->load();

        $devTools = new DevTools();
        $devTools->start();

        (new VarDirectory())->init();
        $devTools->add('var-checked');

        (new Container())->init();
        $devTools->add('booted');

        (new KernelEventRegistration())->register();
        $devTools->add('events-registered');

        $container = new Container();
        $container->get(Security::class)->init();
        $devTools->add('security-started');

        $eventDispatcher = new EventDispatcher();
        $route = $container->get(RouterDispatcher::class)->dispatch();
        $devTools->add('routing-dispatched', $route);

        $eventDispatcher->dispatch(new RequestEvent());
        $devTools->add('kernel-request-dispatched');

        if ($route->status === RouterDispatcherStatusEnum::FOUND) {

            try {
                $devTools->add('controller-started');

                $controller = (new Container())->get($route->class);
                $response = $controller->{$route->action}(...$route->vars);

                $devTools->add('controller-finished');

                $eventDispatcher->dispatch(new ResponseEvent());

                $devTools->add('kernel-response-dispatched');

                $this->render($response);
            } catch (Throwable $throwable) {
                (new Logger())->error($throwable);
                $eventDispatcher->dispatch(new ExceptionEvent($throwable));

                if ($_ENV['APP_ENV'] === 'dev') {
                    $devTools->add('error', $throwable);
                    $devTools->end(true);
                    die();
                }

                throw $throwable;
            }

            die();
        } else {
            throw new \HttpException('Not found', 404);
        }
    }

    private function render($response): void {
        $responseClass = get_class($response);
        $devTools = new DevTools();

        switch ($responseClass) {
            case TemplateResponse::class:
                /** @var $response TemplateResponse */
                echo (new TemplateEngine())->render($response);

                $devTools->add('twig-rendered');
                $devTools->end(true);

                break;
            case JsonResponse::class:
                /** @var $response JsonResponse */
                http_response_code($response->httpCode);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode($response->data);

                $devTools->end(false);

                break;
            default:
                echo 'Controller return unknown type of response';

                break;
        }
    }
}