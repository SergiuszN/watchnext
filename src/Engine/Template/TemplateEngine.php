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
use WatchNext\Engine\Cache\MemcachedCache;
use WatchNext\Engine\Config;
use WatchNext\Engine\Container;
use WatchNext\Engine\Request\Request;
use WatchNext\Engine\Response\CachedTemplateResponse;
use WatchNext\Engine\Response\TemplateResponse;
use WatchNext\Engine\Router\RouteGenerator;
use WatchNext\Engine\Security\CSFR;
use WatchNext\Engine\Security\FlashBag;
use WatchNext\Engine\Security\Security;

class TemplateEngine
{
    private static ?Environment $twig = null;

    /**
     * @throws Exception
     */
    public function __construct(
        Container $container,
        Config $config,
        private readonly MemcachedCache $memcache,
    ) {
        if (self::$twig) {
            return;
        }

        $loader = new FilesystemLoader(ROOT_PATH . '/templates');
        $cache = $_ENV['APP_ENV'] === 'prod' ? ROOT_PATH . '/var/cache/template-cache' : false;
        $debug = $_ENV['APP_ENV'] !== 'prod';

        self::$twig = new Environment($loader, [
            'cache' => $cache,
            'debug' => $debug,
        ]);

        $this->addDefaultGlobals($container);
        $this->addDefaultFunctions($container);
        $this->addUserGlobals($config, $container);
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function render(TemplateResponse|CachedTemplateResponse $templateResponse): string
    {
        if ($templateResponse instanceof TemplateResponse || $_ENV['APP_ENV'] === 'dev') {
            return self::$twig->render($templateResponse->template, $templateResponse->params);
        }

        return $this->memcache->get(
            json_encode($templateResponse),
            fn () => self::$twig->render($templateResponse->template, $templateResponse->params),
            $templateResponse->ttl
        );
    }

    public function warmup(string $template): void
    {
        try {
            self::$twig->render($template);
        } catch (Exception $exception) {
            // Silence is golden
        }
    }

    public function addGlobal(string $name, $value): void
    {
        self::$twig->addGlobal($name, $value);
    }

    public function addFilter(TwigFilter $filter): void
    {
        self::$twig->addFilter($filter);
    }

    public function addFunction(TwigFunction $function): void
    {
        self::$twig->addFunction($function);
    }

    private function addDefaultGlobals(Container $container): void
    {
        $this->addGlobal('flash', $container->get(FlashBag::class));
        $this->addGlobal('t', $container->get(Translator::class));
        $this->addGlobal('csfr', $container->get(CSFR::class));
        $this->addGlobal('asset', $container->get(Asset::class));
        $this->addGlobal('route', $container->get(RouteGenerator::class));
        $this->addGlobal('request', $container->get(Request::class));
        $this->addGlobal('security', $container->get(Security::class));
    }

    private function addDefaultFunctions(Container $container): void
    {
        $this->addFunction(new TwigFunction('dump', function ($data) {
            echo '<pre>';
            var_dump($data);
            echo '</pre>';
        }));
    }

    /**
     * @throws Exception
     */
    private function addUserGlobals(Config $config, Container $container): void
    {
        $globals = $config->get('twigGlobals.php');
        foreach ($globals as $name => $globalClass) {
            $this->addGlobal($name, $container->get($globalClass));
        }
    }
}
