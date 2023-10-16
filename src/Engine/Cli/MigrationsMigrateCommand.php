<?php

namespace WatchNext\Engine\Cli;

use Doctrine\DBAL\Connection;
use WatchNext\Engine\Cli\IO\CliInput;
use WatchNext\Engine\Cli\IO\CliOutput;
use WatchNext\Engine\Config;
use WatchNext\Engine\Container;
use WatchNext\Engine\Database\Database;
use WatchNext\Engine\Database\Migration;

class MigrationsMigrateCommand implements CliCommandInterface {
    private Connection $connection;
    private CliInput $input;
    private CliOutput $output;
    private array $migrations;
    private array $migrated;
    private Container $container;

    public function __construct() {
        $this->container = new Container();
        $this->connection = $this->container->get(Database::class)->getConnection();
        $this->input = new CliInput();
        $this->output = new CliOutput();
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public function execute(): void {
        $this->output->writeln('Migration of database...');

        $this->createMigrationsTableIfNotExist();
        $this->loadMigratedMigrations();
        $this->loadAvailableMigrations();

        $currentVersion = $this->getCurrentVersion();
        $selectedVersion = $this->input->isOptionExist('version')
            ? (int) $this->input->getOption('version')
            : $this->getLastVersionInFiles();

        if ($currentVersion <= $selectedVersion) {
            $this->up($selectedVersion);
        } else {
            $this->down($selectedVersion);
        }

        $this->output->writeln('Done');
    }

    private function up(int $selectedVersion): void {
        $migrationsToMigrate = array_values(array_filter($this->migrations, function ($migration) use ($selectedVersion) {
            return $migration['version'] <= $selectedVersion && $migration['isMigrated'] === false;
        }));

        usort($migrationsToMigrate, fn($left, $right) => $left['version'] <=> $right['version']);

        $statement = $this->connection->prepare("
            INSERT INTO `migrations`(`version`, `name`, `executed_at`) VALUES (:version, :name, NOW());
        ");

        foreach ($migrationsToMigrate as $migration) {
            echo "Migrate: {$migration['name']}...\n";
            $migrationClass = "Migrations\\{$migration['name']}";

            /** @var Migration $migrationObject */
            $migrationObject = new $migrationClass($this->container, $this->connection);
            $migrationObject->up();

            $statement->executeStatement(['version' => $migration['version'], 'name' => $migration['name']]);
        }
    }

    private function down(int $selectedVersion): void {
        $migrationsToMigrate = array_values(array_filter($this->migrations, function ($migration) use ($selectedVersion) {
            return $migration['version'] >= $selectedVersion && $migration['isMigrated'] === true;
        }));

        usort($migrationsToMigrate, fn($left, $right) => $right['version'] <=> $left['version']);

        $statement = $this->connection->prepare("
            DELETE FROM `migrations` WHERE `version`=:version;
        ");

        foreach ($migrationsToMigrate as $migration) {
            echo "Migrate: {$migration['name']}...\n";
            $migrationClass = "Migrations\\{$migration['name']}";

            /** @var Migration $migrationObject */
            $migrationObject = new $migrationClass($this->container, $this->connection);
            $migrationObject->down();

            $statement->executeStatement(['version' => $migration['version']]);
        }
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    private function createMigrationsTableIfNotExist(): void {
        $database = $this->connection->getDatabase();

        $result = $this->connection
            ->prepare("
                SELECT count(*) AS cnt
                FROM information_schema.tables
                WHERE table_schema = :database
                AND table_name = 'migrations'
            ")
            ->executeQuery(['database' => $database]);

        $count = (int) $result->fetchAssociative()['cnt'];

        if ($count === 0) {
            $this->connection->executeStatement("
                CREATE TABLE `migrations` (
                    version INT,
                    name VARCHAR(255),
                    executed_at DATETIME DEFAULT NOW()
                );

                INSERT INTO `migrations` VALUES (0, 'init', NOW());
            ");
        }
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    private function getCurrentVersion(): int {
        return (int) $this->connection
            ->executeQuery("
                SELECT MAX(version) AS current_version FROM `migrations` WHERE 1;
            ")
            ->fetchAssociative()['current_version'];
    }

    private function getLastVersionInFiles(): int {
        return max(array_map(
            fn($migration) => $migration['version'],
            $this->migrations
        ));
    }

    private function loadAvailableMigrations(): void {
        $config = new Config();
        $basePath = "{$config->getRootPath()}/config/migrations";
        $files = scandir($basePath);
        $files = array_diff($files, ['..', '.']);
        $this->migrations = [];

        foreach ($files as $file) {
            $tokens = explode('_', $file);
            $migrationVersion = (int) $tokens[1];
            $migrationName = str_replace('.php', '', $file);

            $this->migrations[$migrationVersion] = [
                'version' => $migrationVersion,
                'name' => $migrationName,
                'path' => $basePath . '/' . $file,
                'isMigrated' => isset($this->migrated[$migrationVersion]),
            ];
        }
    }

    private function loadMigratedMigrations() {
        $result = $this->connection->executeQuery("SELECT * FROM `migrations` WHERE 1;");
        $migrated = $result->fetchAllAssociative();

        $this->migrated = [];
        foreach ($migrated as $migration) {
            $this->migrated[$migration['version']] = $migration;
        }
    }
}