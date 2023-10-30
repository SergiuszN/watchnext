<?php

namespace WatchNext\Engine\Dispatcher;

use Exception;
use JetBrains\PhpStorm\NoReturn;
use WatchNext\Engine\Cache\VarDirectory;
use WatchNext\Engine\Cli\CliCommandInterface;
use WatchNext\Engine\Cli\IO\CliInput;
use WatchNext\Engine\Config;
use WatchNext\Engine\Container;
use WatchNext\Engine\Env;

class CliDispatcher {
    /**
     * @throws Exception
     */
    #[NoReturn] public function dispatch(): void {
        (new Env())->load();
        (new VarDirectory())->check();

        $container = new Container();
        $container->init();

        $commands = (new Config())->get('cli.php');
        $commands = array_merge($commands, $this->getKernelCliCommands());

        global $argv;
        $selectedCommandName = $argv[1] ?? null;

        if ($selectedCommandName === null) {
            echo "Available commands:\n\n";

            foreach ($commands as $commandName => $commandClass) {
                echo "\t$commandName\n";
            }

            die();
        }

        $input = new CliInput();
        $isHelpOptionSelected = $input->isOptionExist('help');

        foreach ($commands as $commandName => $commandClass) {
            if ($commandName === $selectedCommandName) {
                /** @var CliCommandInterface $command */
                $command = $container->get($commandClass);

                if (!$isHelpOptionSelected) {
                    $command->execute();
                } else {
                    echo "Help about '$commandName':\n\n";
                    echo $command->getHelp();
                    echo "\n";
                }

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