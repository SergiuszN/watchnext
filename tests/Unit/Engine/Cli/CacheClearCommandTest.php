<?php

namespace Unit\Engine\Cli;

use PHPUnit\Framework\TestCase;
use WatchNext\Engine\Cli\CacheClearCommand;
use WatchNext\Engine\Cli\IO\CliInput;
use WatchNext\Engine\Cli\IO\CliOutput;
use WatchNext\Engine\Container;

class CacheClearCommandTest extends TestCase
{
    public function testExecute(): void
    {
        [$input, $output] = [new CliInput(), new CliOutput()];
        $input->setArgv(['php', 'console.php', 'cache:clear']);

        $container = new Container();
        $command = $container->get(CacheClearCommand::class);
        $command->execute($input, $output);
        self::assertFileDoesNotExist(__DIR__ . '/../../../../var/cache/template-cache');

        $input->setArgv(['php', 'console.php', 'cache:clear', '--warmup']);
        $command->execute($input, $output);
        self::assertFileExists(__DIR__ . '/../../../../var/cache/template-cache');
        self::assertIsString($command->getHelp());
    }
}
