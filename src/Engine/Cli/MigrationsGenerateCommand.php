<?php

namespace WatchNext\Engine\Cli;

use WatchNext\Engine\Cli\IO\CliInput;
use WatchNext\Engine\Cli\IO\CliOutput;

class MigrationsGenerateCommand implements CliCommandInterface
{
    public function getHelp(): string
    {
        return 'This command creates empty migration template with selected name
Required arguments:
    migrations:generate {NameWhatYouNeedForNewCommand}
';
    }

    public function execute(CliInput $input, CliOutput $output): void
    {
        $rootPath = ROOT_PATH;
        $name = $input->getArgument(0, null, true, "You must provide 'name'(0) argument!");

        $output->writeln("Creation of '$name' migration...");

        $file = file_get_contents("{$rootPath}/src/Engine/Database/ExampleMigration.php.text");
        $className = 'm_' . time() . '_' . $name;

        $file = str_replace('%className%', $className, $file);
        file_put_contents("{$rootPath}/config/migrations/{$className}.php", $file);

        $output->writeln('Done!');
    }
}
