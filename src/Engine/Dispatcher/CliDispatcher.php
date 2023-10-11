<?php

namespace WatchNext\Engine\Dispatcher;

use Exception;
use JetBrains\PhpStorm\NoReturn;
use WatchNext\Engine\Cli\CliCommandInterface;

class CliDispatcher {
    /**
     * @throws Exception
     */
    #[NoReturn] public function dispatch(): void {
        (new InternalDispatcher())->dispatch();

        $commands = require __DIR__ . '/../../../config/cli.php';
        global $argv;
        $selectedCommandName = $argv[1];

        foreach ($commands as $commandName => $commandClass) {
            if ($commandName === $selectedCommandName) {
                /** @var CliCommandInterface $command */
                $command = new $commandClass();
                $command->execute();
                die();
            }
        }

        echo "There no command with name '$selectedCommandName'\n";
        die();
    }
}