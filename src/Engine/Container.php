<?php

namespace WatchNext\Engine;

use DI\Container as DIContainer;
use DI\ContainerBuilder;
use Exception;
use WatchNext\Engine\Database\Database;
use WatchNext\Engine\Event\EventDispatcher;
use WatchNext\Engine\Router\RouteGenerator;
use WatchNext\Engine\Session\Auth;
use WatchNext\Engine\Session\FlashBag;
use WatchNext\Engine\Session\Security;
use WatchNext\Engine\Template\Language;
use WatchNext\Engine\Template\TemplateEngine;
use function DI\autowire;

class Container {
    private static ?DIContainer $diContainer = null;

    /**
     * @throws Exception
     */
    public function init(): void {
        if (self::$diContainer) {
            throw new Exception('Container already created!');
        }

        $env = $_ENV['APP_ENV'];
        $config = new Config();

        $kernelConfig = $this->getKernelDI();
        $baseConfig = $config->get('di/di.php');
        $envConfig = $config->get("di/di.{$env}.php");

        $builder = new ContainerBuilder();
        $builder->addDefinitions(array_merge($kernelConfig, $baseConfig, $envConfig));

        if ($env === 'prod') {
            $builder->enableCompilation("{$config->getCachePath()}/di-cache");
        }

        self::$diContainer = $builder->build();
    }

    /**
     * Returns an entry of the container by its name.
     *
     * @template T
     * @param string|class-string<T> $id Entry name or a class name.
     *
     * @return mixed|T
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function get(string $id): mixed {
        /** @noinspection PhpUnhandledExceptionInspection */
        return self::$diContainer->get($id);
    }

    private function getKernelDI(): array {
        return [
            'root.dir' => (new Config())->getRootPath(),

            Container::class => fn () => new Container(),
            Database::class => fn () => new Database(),
            TemplateEngine::class => fn () => new TemplateEngine(),

            RouteGenerator::class => autowire(RouteGenerator::class),

            Logger::class => fn() => new Logger(),
            EventDispatcher::class => fn () => new EventDispatcher(),
            Language::class => fn () => new Language(),

            FlashBag::class => fn () => new FlashBag(),
            Auth::class => fn () => autowire(Auth::class),
            Security::class => fn () => new Security(),
        ];
    }
}