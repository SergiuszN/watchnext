<?php

namespace WatchNext\Engine\Dispatcher;

use Exception;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use WatchNext\Engine\Container;
use WatchNext\Engine\Profiler;
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
use WatchNext\Engine\Template\TemplateEngine;
use WatchNext\WatchNext\Application\Controller\SecurityController;

class HttpDispatcher {
    public function __construct(
        private Profiler           $profiler,
        private Security           $security,
        private RouterDispatcher   $routerDispatcher,
        private EventManager       $eventManager,
        private SecurityController $securityController,
        private TemplateEngine     $templateEngine,
        private RouteGenerator     $routeGenerator,
        private Container          $container,
        private Logger             $logger
    ) {
    }

    /**
     * @throws Exception|Throwable
     */
    public function dispatch(): void {
        $this->profiler->start();
        $this->profiler->add('kernel.booted');

        $this->security->init();
        $this->profiler->add('security.booted');

        $route = $this->routerDispatcher->dispatch();
        $this->profiler->add('route.dispatched');

        $this->eventManager->init();

        if ($route->status === RouterDispatcherStatusEnum::FOUND) {
            try {
                $this->security->throwIfPathNotAccessible($_SERVER['REQUEST_URI']);

                $controller = $this->container->get($route->class);
                $response = $controller->{$route->action}(...$route->vars);
                $this->profiler->add('controller.dispatched');

                $this->render($response);
            } catch (AccessDeniedException $accessDeniedException) {
                $this->render($this->securityController->accessDenied());

                die();
            } catch (NotFoundException $notFoundException) {
                $this->render($this->securityController->notFound());

                die();
            } catch (Throwable $throwable) {
                $this->logger->error($throwable);
                throw $throwable;
            }

            die();
        } else {
            $this->render($this->securityController->notFound());
        }
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     * @throws Exception
     */
    private function render($response): void {
        $responseClass = get_class($response);
        $printProfile = false;

        switch ($responseClass) {
            case TemplateResponse::class:
                /** @var $response TemplateResponse */
                echo $this->templateEngine->render($response);

                $printProfile = true;
                break;
            case RedirectResponse::class:
                /** @var $response RedirectResponse */
                $location = $this->routeGenerator->make($response->route, $response->params);
                header("Location: $location");

                break;
            case JsonResponse::class:
                /** @var $response JsonResponse */
                http_response_code($response->httpCode);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode($response->data);

                break;
            default:
                break;
        }

        $this->profiler->add('kernel.rendered');
        $this->profiler->end($printProfile);
    }
}