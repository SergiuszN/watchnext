<?php

namespace WatchNext\Engine\Cli;

use Exception;
use WatchNext\Engine\Cache\ApcuCache;
use WatchNext\Engine\Cache\MemcachedCache;
use WatchNext\Engine\Cli\IO\CliInput;
use WatchNext\Engine\Cli\IO\CliOutput;
use WatchNext\Engine\Container;
use WatchNext\Engine\Router\RouterDispatcher;
use WatchNext\Engine\Template\TemplateEngine;

readonly class CacheClearCommand implements CliCommandInterface
{
    public function __construct(
        private Container $container,
        private TemplateEngine $templateEngine,
        private RouterDispatcher $dispatcher,
        private MemcachedCache $memcachedCache,
        private ApcuCache $apcuCache,
    ) {
    }

    public function getHelp(): string
    {
        return 'That command clears filesystem cache
If you need warmup cache after clearing you can add option
    --warmup
';
    }

    /**
     * @throws Exception
     */
    public function execute(): void
    {
        $input = new CliInput();
        $output = new CliOutput();

        $output->write('Clearing the filesystem cache...');
        $cacheFolder = ROOT_PATH . '/var/cache/*';
        exec("rm -rf $cacheFolder");
        $output->writeln(' OK');

        $output->write('Clearing the cache...');
        $this->memcachedCache->clearAll();
        $this->apcuCache->clearAll();
        $output->writeln(' OK');

        if ($input->isOptionExist('warmup')) {
            $output->writeln('Warmup...');

            $output->writeln("\t twig building...");
            $baseTemplatePath = realpath(__DIR__ . '/../../../templates');
            $this->getDirContents(__DIR__ . '/../../../templates', $templates);
            foreach ($templates as $template) {
                $this->templateEngine->warmup(str_replace($baseTemplatePath, '', $template));
            }

            $output->writeln("\t routes loading...");
            $this->dispatcher->warmup();

            $output->writeln("\t container building...");
            $this->container->warmup();
        }

        $output->writeln('Done!');
    }

    private function getDirContents($dir, &$results = []): void
    {
        $files = scandir($dir);

        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path)) {
                if (str_contains($path, '.html.twig')) {
                    $results[] = $path;
                }
            } elseif ($value != '.' && $value != '..') {
                $this->getDirContents($path, $results);
            }
        }
    }
}
