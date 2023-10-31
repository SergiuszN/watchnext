<?php

namespace WatchNext\Engine;

use DI\Container as DIContainer;
use DI\ContainerBuilder;
use Exception;
use WatchNext\Engine\Cache\ApcuCache;
use WatchNext\Engine\Cache\MemcachedCache;
use WatchNext\Engine\Cli\CacheClearCommand;
use WatchNext\Engine\Cli\MigrationsGenerateCommand;
use WatchNext\Engine\Cli\MigrationsMigrateCommand;
use WatchNext\Engine\Cli\TranslatorOrderKeysCommand;
use WatchNext\Engine\Database\Database;
use WatchNext\Engine\Dispatcher\HttpDispatcher;
use WatchNext\Engine\Event\EventManager;
use WatchNext\Engine\Request\Request;
use WatchNext\Engine\Router\RouteGenerator;
use WatchNext\Engine\Session\Auth;
use WatchNext\Engine\Session\Firewall;
use WatchNext\Engine\Session\FlashBag;
use WatchNext\Engine\Session\Security;
use WatchNext\Engine\Template\Asset;
use WatchNext\Engine\Template\Language;
use WatchNext\Engine\Template\TemplateEngine;

use function DI\autowire;

class Container
{
    private static ?DIContainer $diContainer = null;

    /**
     * @throws Exception
     */
    public function init(): self
    {
        if (self::$diContainer) {
            throw new Exception('Container already created!');
        }

        $this->build();

        return $this;
    }

    /**
     * @throws Exception
     */
    private function build(): void
    {
        $env = ENV;
        $config = new Config();

        $kernelConfig = $this->getKernelDI();
        $baseConfig = $config->get('di/di.php');
        $envConfig = $config->get("di/di.{$env}.php");

        $builder = new ContainerBuilder();
        $builder->addDefinitions(array_merge($kernelConfig, $baseConfig, $envConfig));

        if ($env === 'prod') {
            $builder->enableCompilation(ROOT_PATH . '/var/cache/di-cache');
            $builder->writeProxiesToFile(true, ROOT_PATH . '/var/cache/di-cache/proxies');
        }

        self::$diContainer = $builder->build();
    }

    /**
     * @throws Exception
     */
    public function warmup(): void
    {
        $this->build();
    }

    /**
     * Returns an entry of the container by its name.
     *
     * @template T
     *
     * @param string|class-string<T> $id entry name or a class name
     *
     * @return mixed|T
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function get(string $id): mixed
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return self::$diContainer->get($id);
    }

    private function getKernelDI(): array
    {
        return [
            'rootDir' => ROOT_PATH,

            Container::class => autowire(Container::class),
            HttpDispatcher::class => autowire(HttpDispatcher::class),
            Database::class => autowire(Database::class),
            TemplateEngine::class => autowire(TemplateEngine::class),
            Asset::class => autowire(Asset::class),
            Request::class => autowire(Request::class),
            RouteGenerator::class => autowire(RouteGenerator::class),
            Logger::class => autowire(Logger::class),
            Language::class => autowire(Language::class),
            FlashBag::class => autowire(FlashBag::class),
            Auth::class => autowire(Auth::class),
            Security::class => autowire(Security::class),
            Firewall::class => autowire(Firewall::class),
            EventManager::class => autowire(EventManager::class),
            CacheClearCommand::class => autowire(CacheClearCommand::class),
            MigrationsGenerateCommand::class => autowire(MigrationsGenerateCommand::class),
            MigrationsMigrateCommand::class => autowire(MigrationsMigrateCommand::class),
            TranslatorOrderKeysCommand::class => autowire(TranslatorOrderKeysCommand::class),
            MemcachedCache::class => autowire(MemcachedCache::class),
            ApcuCache::class => autowire(ApcuCache::class),
        ];
    }
}
