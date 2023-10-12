<?php

namespace WatchNext\Engine\Cli;

use WatchNext\Engine\Cli\IO\CliInput;
use WatchNext\Engine\Cli\IO\CliOutput;

class MigrationsGenerateCommand implements CliCommandInterface {
    public function execute(): void {
        [$input, $output] = [new CliInput(), new CliOutput()];

        $name = $input->getOption('name');

        $output->writeln("Creation of '$name' migration...");

        $file = file_get_contents(__DIR__ . '/../Database/ExampleMigration.php.text');
        $className = 'm_' . time() . '_' . $name;

        $file = str_replace('%className%', $className, $file);
        file_put_contents(__DIR__ . '/../../../config/migrations/' . $className . '.php', $file);

        $output->writeln('Done!');
    }
}