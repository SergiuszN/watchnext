<?php

namespace WatchNext\Engine\Dispatcher;

use Exception;
use Throwable;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use WatchNext\Engine\Container;
use WatchNext\Engine\Event\SyncEventDispatcher;
use WatchNext\Engine\Logger;
use WatchNext\Engine\Profiler;
use WatchNext\Engine\Response\CachedTemplateResponse;
use WatchNext\Engine\Response\JsonResponse;
use WatchNext\Engine\Response\RedirectRefererResponse;
use WatchNext\Engine\Response\RedirectResponse;
use WatchNext\Engine\Response\TemplateResponse;
use WatchNext\Engine\Router\AccessDeniedException;
use WatchNext\Engine\Router\NotFoundException;
use WatchNext\Engine\Router\RouteGenerator;
use WatchNext\Engine\Router\RouterDispatcher;
use WatchNext\Engine\Router\RouterDispatcherStatusEnum;
use WatchNext\Engine\Security\Security;
use WatchNext\Engine\Template\TemplateEngine;
use WatchNext\Engine\Template\Translator;
use WatchNext\WatchNext\Application\Controller\SecurityController;

readonly class HttpDispatcher
{
    public function __construct(
        private Profiler $profiler,
        private Security $security,
        private RouterDispatcher $routerDispatcher,
        private SecurityController $securityController,
        private TemplateEngine $templateEngine,
        private Translator $translator,
        private RouteGenerator $routeGenerator,
        private Container $container,
        private Logger $logger
    ) {
    }

    /**
     * @throws Exception|Throwable
     */
    public function dispatch(): void
    {
        $this->profiler->start('kernel.booted');

        $this->security->init();
        $this->translator->init($this->security->getUser());
        $this->profiler->add('security.booted');

        $route = $this->routerDispatcher->dispatch();
        $this->profiler->add('route.dispatched');

        $this->container->get(SyncEventDispatcher::class);

        if ($route->status === RouterDispatcherStatusEnum::FOUND) {
            try {
                $this->security->throwIfPathNotAccessible($_SERVER['REQUEST_URI']);

                $controller = $this->container->get($route->class);
                $response = $controller->{$route->action}(...$route->vars);
                $this->profiler->add('controller.dispatched');

                $this->render($response);
            } catch (AccessDeniedException $accessDeniedException) {
                http_response_code(401);
                $this->render($this->securityController->accessDenied());

                return;
            } catch (NotFoundException $notFoundException) {
                http_response_code(404);
                $this->render($this->securityController->notFound());

                return;
            } catch (Throwable $throwable) {
                http_response_code(500);
                $this->logger->error($throwable);

                if ($_ENV['APP_ENV'] === 'dev') {
                    throw $throwable;
                }
            }

            return;
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
    private function render($response): void
    {
        $responseClass = get_class($response);
        $printProfile = false;

        switch ($responseClass) {
            case CachedTemplateResponse::class:
            case TemplateResponse::class:
                /** @var $response TemplateResponse|CachedTemplateResponse */
                http_response_code(200);
                echo $this->templateEngine->render($response);

                $printProfile = true;
                break;
            case RedirectResponse::class:
                /** @var $response RedirectResponse */
                $location = $this->routeGenerator->make($response->route, $response->params);

                http_response_code(302);
                header("Location: $location");

                break;
            case RedirectRefererResponse::class:
                /** @var $response RedirectRefererResponse */
                http_response_code(302);
                header("Location: $response->uri");

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
