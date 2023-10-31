<?php

namespace WatchNext\Engine\Template;

use Exception;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;
use WatchNext\Engine\Config;
use WatchNext\Engine\Container;
use WatchNext\Engine\Request\Request;
use WatchNext\Engine\Response\TemplateResponse;
use WatchNext\Engine\Router\RouteGenerator;
use WatchNext\Engine\Session\CSFR;
use WatchNext\Engine\Session\FlashBag;
use WatchNext\Engine\Session\Security;

class TemplateEngine {
    private static ?Environment $twig = null;

    /**
     * @throws Exception
     */
    public function __construct(Container $container, Config $config) {
        if (self::$twig) {
            return;
        }

        $loader = new FilesystemLoader(ROOT_PATH . '/templates');
        $cache = $_ENV['APP_ENV'] === 'dev' ? false : ROOT_PATH . '/var/cache/template-cache';
        $debug = $_ENV['APP_ENV'] === 'dev';

        self::$twig = new Environment($loader, [
            'cache' => $cache,
            'debug' => $debug,
        ]);

        $this->addDefaultGlobals($container);
        $this->addUserGlobals($config, $container);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function render(TemplateResponse $templateResponse): string {
        return self::$twig->render($templateResponse->template, $templateResponse->params);
    }

    public function warmup(string $template): void {
        try {
            self::$twig->render($template);
        } catch (Exception $exception) {
            // Silence is golden
        }
    }

    public function addGlobal(string $name, $value): void {
        self::$twig->addGlobal($name, $value);
    }

    public function addFilter(TwigFilter $filter): void {
        self::$twig->addFilter($filter);
    }

    public function addFunction(TwigFunction $function): void {
        self::$twig->addFunction($function);
    }

    private function addDefaultGlobals(Container $container): void {
        $this->addGlobal('flash', $container->get(FlashBag::class));
        $this->addGlobal('t', $container->get(Language::class));
        $this->addGlobal('csfr', $container->get(CSFR::class));
        $this->addGlobal('asset', $container->get(Asset::class));
        $this->addGlobal('route', $container->get(RouteGenerator::class));
        $this->addGlobal('request', $container->get(Request::class));
        $this->addGlobal('security', $container->get(Security::class));
    }

    /**
     * @throws Exception
     */
    private function addUserGlobals(Config $config, Container $container): void {
        $globals = $config->get('twigGlobals.php');
        foreach ($globals as $name => $globalClass) {
            $this->addGlobal($name, $container->get($globalClass));
        }
    }
}