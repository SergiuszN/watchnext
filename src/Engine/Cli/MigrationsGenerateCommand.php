<?php

namespace WatchNext\Engine\Cli;

use WatchNext\Engine\Cli\IO\CliInput;
use WatchNext\Engine\Cli\IO\CliOutput;
use WatchNext\Engine\Config;

class MigrationsGenerateCommand implements CliCommandInterface {
    public function execute(): void {
        [$input, $output] = [new CliInput(), new CliOutput()];
        $rootPath = (new Config())->getRootPath();

        $name = $input->getOption('name');

        $output->writeln("Creation of '$name' migration...");

        $file = file_get_contents( "{$rootPath}/src/Engine/Database/ExampleMigration.php.text");
        $className = 'm_' . time() . '_' . $name;

        $file = str_replace('%className%', $className, $file);
        file_put_contents("{$rootPath}/config/migrations/{$className}.php", $file);

        $output->writeln('Done!');
    }
}