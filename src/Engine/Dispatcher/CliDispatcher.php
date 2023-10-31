<?php

namespace WatchNext\Engine\Dispatcher;

use Exception;
use JetBrains\PhpStorm\NoReturn;
use WatchNext\Engine\Cli\CliCommandInterface;
use WatchNext\Engine\Cli\IO\CliInput;
use WatchNext\Engine\Config;
use WatchNext\Engine\Container;

readonly class CliDispatcher
{
    public function __construct(
        private Config $config,
        private Container $container,
    ) {
    }

    /**
     * @throws Exception
     */
    #[NoReturn]
    public function dispatch(): void
    {
        $commands = $this->config->get('cli.php');
        $commands = array_merge($commands, $this->getKernelCliCommands());

        global $argv;
        $selectedCommandName = $argv[1] ?? null;

        if ($selectedCommandName === null) {
            echo "Available commands:\n\n";

            foreach ($commands as $commandName => $commandClass) {
                echo "\t$commandName\n";
            }

            exit;
        }

        $input = new CliInput();
        $isHelpOptionSelected = $input->isOptionExist('help');

        foreach ($commands as $commandName => $commandClass) {
            if ($commandName === $selectedCommandName) {
                /** @var CliCommandInterface $command */
                $command = $this->container->get($commandClass);

                if (!$isHelpOptionSelected) {
                    $command->execute();
                } else {
                    echo "Help about '$commandName':\n\n";
                    echo $command->getHelp();
                    echo "\n";
                }

                exit;
            }
        }

        echo "There no command with name '$selectedCommandName'\n";
        exit;
    }

    private function getKernelCliCommands(): array
    {
        return [
            'cache:clear' => \WatchNext\Engine\Cli\CacheClearCommand::class,
            'migrations:generate' => \WatchNext\Engine\Cli\MigrationsGenerateCommand::class,
            'migrations:migrate' => \WatchNext\Engine\Cli\MigrationsMigrateCommand::class,
            'translations:reorder' => \WatchNext\Engine\Cli\TranslatorOrderKeysCommand::class,
            'translations:check' => \WatchNext\Engine\Cli\TranslatorCheckCommand::class,
        ];
    }
}
