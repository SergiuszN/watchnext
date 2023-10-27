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
use WatchNext\Engine\Session\Auth;
use WatchNext\Engine\Session\CSFR;
use WatchNext\Engine\Session\FlashBag;

class TemplateEngine {
    private static ?Environment $twig = null;

    public function __construct() {
        if (self::$twig) {
            return;
        }

        $config = new Config();
        $loader = new FilesystemLoader($config->getRootPath() . '/templates');
        $cache = $_ENV['APP_ENV'] === 'dev' ? false : $config->getCachePath() . '/template-cache';
        $debug = $_ENV['APP_ENV'] === 'dev';

        self::$twig = new Environment($loader, [
            'cache' => $cache,
            'debug' => $debug,
        ]);

        $this->addDefaultGlobals();
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

    private function addDefaultGlobals(): void {
        $this->addGlobal('flash', new FlashBag());
        $this->addGlobal('t', new Language());
        $this->addGlobal('csfr', new CSFR());
        $this->addGlobal('asset', (new Container())->get(Asset::class));
        $this->addGlobal('route', new RouteGenerator());
        $this->addGlobal('request', new Request());
        $this->addGlobal('auth', new Auth());
    }
}