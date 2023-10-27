<?php

namespace WatchNext\Engine;

use DI\Container as DIContainer;
use DI\ContainerBuilder;
use Exception;
use WatchNext\Engine\Cli\CacheClearCommand;
use WatchNext\Engine\Cli\MigrationsGenerateCommand;
use WatchNext\Engine\Cli\MigrationsMigrateCommand;
use WatchNext\Engine\Cli\TranslatorOrderKeysCommand;
use WatchNext\Engine\Database\Database;
use WatchNext\Engine\Event\EventDispatcher;
use WatchNext\Engine\Mail\Mailer;
use WatchNext\Engine\Request\Request;
use WatchNext\Engine\Router\RouteGenerator;
use WatchNext\Engine\Session\Auth;
use WatchNext\Engine\Session\FlashBag;
use WatchNext\Engine\Session\Security;
use WatchNext\Engine\Session\SecurityFirewall;
use WatchNext\Engine\Template\Asset;
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

        $this->build();
    }

    private function build(): void {
        $env = $_ENV['APP_ENV'];
        $config = new Config();

        $kernelConfig = $this->getKernelDI();
        $baseConfig = $config->get('di/di.php');
        $envConfig = $config->get("di/di.{$env}.php");

        $builder = new ContainerBuilder();
        $builder->addDefinitions(array_merge($kernelConfig, $baseConfig, $envConfig));

        if ($env === 'prod') {
            $builder->enableCompilation("{$config->getCachePath()}/di-cache");
            $builder->writeProxiesToFile(true, "{$config->getCachePath()}/di-cache/proxies");
        }

        self::$diContainer = $builder->build();
    }

    public function warmup(): void {
        $this->build();
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

            Container::class => fn() => new Container(),
            Database::class => fn() => new Database(),
            TemplateEngine::class => fn() => new TemplateEngine(),
            Asset::class => autowire(Asset::class),
            Request::class => fn() => new Request(),

            RouteGenerator::class => autowire(RouteGenerator::class),

            Logger::class => fn() => fn() => new Logger(),
            EventDispatcher::class => fn() => new EventDispatcher(),
            Language::class => fn() => new Language(),
            Mailer::class => fn() => new Mailer(),

            FlashBag::class => fn() => new FlashBag(),
            Auth::class => fn() => new Auth(),
            Security::class => autowire(Security::class),
            SecurityFirewall::class => autowire(SecurityFirewall::class),

            CacheClearCommand::class => autowire(CacheClearCommand::class),
            MigrationsGenerateCommand::class => autowire(MigrationsGenerateCommand::class),
            MigrationsMigrateCommand::class => autowire(MigrationsMigrateCommand::class),
            TranslatorOrderKeysCommand::class => autowire(TranslatorOrderKeysCommand::class),
        ];
    }
}