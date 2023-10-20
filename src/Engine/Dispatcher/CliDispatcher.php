<?php

namespace WatchNext\Engine\Dispatcher;

use Exception;
use JetBrains\PhpStorm\NoReturn;
use WatchNext\Engine\Cache\VarDirectory;
use WatchNext\Engine\Cli\CliCommandInterface;
use WatchNext\Engine\Config;
use WatchNext\Engine\Container;
use WatchNext\Engine\Env;

class CliDispatcher {
    /**
     * @throws Exception
     */
    #[NoReturn] public function dispatch(): void {
        (new Env())->load();
        (new VarDirectory())->init();

        $container = new Container();
        $container->init();

        $commands = (new Config())->get('cli.php');
        $commands = array_merge($commands, $this->getKernelCliCommands());

        global $argv;
        $selectedCommandName = $argv[1];

        foreach ($commands as $commandName => $commandClass) {
            if ($commandName === $selectedCommandName) {
                /** @var CliCommandInterface $command */
                $command = $container->get($commandClass);
                $command->execute();
                die();
            }
        }

        echo "There no command with name '$selectedCommandName'\n";
        die();
    }

    private function getKernelCliCommands(): array {
        return [
            'cache:clear' => \WatchNext\Engine\Cli\CacheClearCommand::class,
            'migrations:generate' => \WatchNext\Engine\Cli\MigrationsGenerateCommand::class,
            'migrations:migrate' => \WatchNext\Engine\Cli\MigrationsMigrateCommand::class,
            'translations:reorder' => \WatchNext\Engine\Cli\TranslatorOrderKeysCommand::class,
            'translations:check' => \WatchNext\Engine\Cli\TranslatorCheckCommand::class,
        ];
    }
}