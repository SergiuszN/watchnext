<?php

namespace WatchNext\Engine\Cli;

use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\Command\GenerateCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use WatchNext\Engine\Database;

class GenerateMigrationCommand implements CliCommandInterface {
    /**
     * @throws ExceptionInterface
     */
    public function execute(): void {

        $config = new PhpFile(__DIR__ . '/../../../config/migration.php');
        $dependencyFactory = DependencyFactory::fromConnection($config, new ExistingConnection(Database::getConnection()));

        $cli = new Application('Doctrine Migrations');
        $cli->setCatchExceptions(true);

        (new GenerateCommand($dependencyFactory))
            ->run(new ArgvInput(), new ConsoleOutput());
    }
}