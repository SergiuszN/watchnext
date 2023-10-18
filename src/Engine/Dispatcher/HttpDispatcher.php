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
use WatchNext\Engine\Response\RedirectResponse;
use WatchNext\Engine\Response\TemplateResponse;
use WatchNext\Engine\Router\RouteGenerator;
use WatchNext\Engine\Router\RouterDispatcher;
use WatchNext\Engine\Router\RouterDispatcherStatusEnum;
use WatchNext\Engine\Session\Auth;
use WatchNext\Engine\Session\Security;
use WatchNext\Engine\Template\TemplateEngine;

class HttpDispatcher {
    /**
     * @throws Exception|Throwable
     */
    public function dispatch(): void {
        (new Env())->load();
        (new VarDirectory())->init();

        $devTools = new DevTools();
        $devTools->start();

        (new Container())->init();
        (new KernelEventRegistration())->register();

        $devTools->add('container.booted');

        $container = new Container();
        $container->get(Security::class)->init();

        $devTools->add('security.booted');

        $eventDispatcher = new EventDispatcher();
        $route = $container->get(RouterDispatcher::class)->dispatch();

        $devTools->add('route.dispatched');

        $eventDispatcher->dispatch(new RequestEvent());

        $devTools->add('request.events.dispatched');

        if ($route->status === RouterDispatcherStatusEnum::FOUND) {

            try {
                $controller = (new Container())->get($route->class);
                $response = $controller->{$route->action}(...$route->vars);

                $devTools->add('controller.dispatched');

                $eventDispatcher->dispatch(new ResponseEvent());

                $devTools->add('response.events.dispatched');

                $this->render($response, $devTools);
            } catch (Throwable $throwable) {
                (new Logger())->error($throwable);
                $eventDispatcher->dispatch(new ExceptionEvent($throwable));

                throw $throwable;
            }

            die();
        } else {
            $devTools->end(false);
            throw new \HttpException('Not found', 404);
        }
    }

    private function render($response, DevTools $devTools): void {
        $responseClass = get_class($response);

        switch ($responseClass) {
            case TemplateResponse::class:
                /** @var $response TemplateResponse */
                echo (new TemplateEngine())->render($response);

                $devTools->add('twig.rendered');
                $devTools->end(true);

                break;
            case RedirectResponse::class:
                /** @var $response RedirectResponse */
                $location = (new RouteGenerator())->make($response->route, $response->params);
                header("Location: $location");

                $devTools->end(false);

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