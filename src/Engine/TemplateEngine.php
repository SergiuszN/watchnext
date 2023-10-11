<?php

namespace WatchNext\Engine;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;
use WatchNext\Engine\Response\TemplateResponse;

class TemplateEngine {
    private static ?Environment $twig = null;

    public static function init(): void {
        if (self::$twig) {
            return;
        }

        $loader = new FilesystemLoader(__DIR__ . '/../../templates');
        $cache = $_ENV['APP_ENV'] === 'dev' ? false : __DIR__ . '/../../var/template-cache';
        $debug = $_ENV['APP_ENV'] === 'dev';

        self::$twig = new Environment($loader, [
            'cache' => $cache,
            'debug' => $debug,
        ]);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function render(TemplateResponse $templateResponse): string {
        return self::$twig->render($templateResponse->template, $templateResponse->params);
    }
}