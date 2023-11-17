<?php

namespace WatchNext\Engine;

use WatchNext\Engine\Router\RouteGenerator;
use WatchNext\Engine\Router\RouterDispatcher;
use WatchNext\Engine\Security\Security;
use WatchNext\Engine\Template\TemplateEngine;
use WatchNext\WatchNext\Application\Controller\SecurityController;

class TestClient
{
    public function __construct(
        private Profiler $profiler,
        private Security $security,
        private RouterDispatcher $routerDispatcher,
        private SecurityController $securityController,
        private TemplateEngine $templateEngine,
        private RouteGenerator $routeGenerator,
        private Container $container,
        private Logger $logger
    ) {
    }


}