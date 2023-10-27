<?php

namespace WatchNext\Engine\Dispatcher;

use Exception;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use WatchNext\Engine\Cache\VarDirectory;
use WatchNext\Engine\Config;
use WatchNext\Engine\Container;
use WatchNext\Engine\DevTools;
use WatchNext\Engine\Env;
use WatchNext\Engine\Event\EventManager;
use WatchNext\Engine\Logger;
use WatchNext\Engine\Response\JsonResponse;
use WatchNext\Engine\Response\RedirectResponse;
use WatchNext\Engine\Response\TemplateResponse;
use WatchNext\Engine\Router\AccessDeniedException;
use WatchNext\Engine\Router\NotFoundException;
use WatchNext\Engine\Router\RouteGenerator;
use WatchNext\Engine\Router\RouterDispatcher;
use WatchNext\Engine\Router\RouterDispatcherStatusEnum;
use WatchNext\Engine\Session\Security;
use WatchNext\Engine\Session\SecurityFirewall;
use WatchNext\Engine\Template\TemplateEngine;
use WatchNext\WatchNext\Application\Controller\SecurityController;

class HttpDispatcher {
    private Container $container;

    /**
     * @throws Exception|Throwable
     */
    public function dispatch(): void {
        (new Env())->load();
        (new VarDirectory())->check();

        $devTools = new DevTools();
        $devTools->start();

        $this->container = new Container();
        $this->container->init();
        $devTools->add('container.booted');

        $this->container->get(Security::class)->init();
        $devTools->add('security.booted');

        $route = $this->container->get(RouterDispatcher::class)->dispatch();
        $devTools->add('route.dispatched');

        $this->container->get(EventManager::class)->init(new Config());

        if ($route->status === RouterDispatcherStatusEnum::FOUND) {
            try {
                $firewall = $this->container->get(SecurityFirewall::class);
                $firewall->throwIfPathNotAccessible($_SERVER['REQUEST_URI']);

                $controller = (new Container())->get($route->class);
                $response = $controller->{$route->action}(...$route->vars);

                $devTools->add('controller.dispatched');

                $this->render($response, $devTools);
            }
            catch (AccessDeniedException $accessDeniedException) {
                $securityController = $this->container->get(SecurityController::class);
                $this->render($securityController->accessDenied(), $devTools);

                die();
            }
            catch (NotFoundException $notFoundException) {
                $securityController = $this->container->get(SecurityController::class);
                $this->render($securityController->notFound(), $devTools);

                die();
            }
            catch (Throwable $throwable) {
                (new Logger())->error($throwable);
                throw $throwable;
            }

            die();
        } else {
            $securityController = $this->container->get(SecurityController::class);
            $this->render($securityController->notFound(), $devTools);
        }
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     * @throws Exception
     */
    private function render($response, DevTools $devTools): void {
        $responseClass = get_class($response);

        switch ($responseClass) {
            case TemplateResponse::class:
                /** @var $response TemplateResponse */
                echo $this->container->get(TemplateEngine::class)->render($response);

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