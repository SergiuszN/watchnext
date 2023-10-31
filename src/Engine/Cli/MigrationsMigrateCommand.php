<?php

namespace WatchNext\Engine\Cli;

use WatchNext\Engine\Cli\IO\CliInput;
use WatchNext\Engine\Cli\IO\CliOutput;
use WatchNext\Engine\Container;
use WatchNext\Engine\Database\Database;
use WatchNext\Engine\Database\Migration;

class MigrationsMigrateCommand implements CliCommandInterface {
    private CliInput $input;
    private CliOutput $output;
    private array $migrations;
    private array $migrated;

    public function __construct(
        private readonly Container $container,
        private readonly Database $database
    ) {
        $this->input = new CliInput();
        $this->output = new CliOutput();
    }

    public function getHelp(): string {
        return 'This command run migrations on selected database
You can specify some version to do or partial up or down grade
For that just add --version=VERSION_NUMBER
';
    }

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

        $statement = $this->database->prepare("
            INSERT INTO `migrations`(`version`, `name`, `executed_at`) VALUES (:version, :name, NOW());
        ");

        foreach ($migrationsToMigrate as $migration) {
            echo "Migrate: {$migration['name']}...\n";
            $migrationClass = "Migrations\\{$migration['name']}";

            /** @var Migration $migrationObject */
            $migrationObject = new $migrationClass($this->container, $this->database);
            $migrationObject->up();

            $statement->execute(['version' => $migration['version'], 'name' => $migration['name']]);
        }
    }

    private function down(int $selectedVersion): void {
        $migrationsToMigrate = array_values(array_filter($this->migrations, function ($migration) use ($selectedVersion) {
            return $migration['version'] >= $selectedVersion && $migration['isMigrated'] === true;
        }));

        usort($migrationsToMigrate, fn($left, $right) => $right['version'] <=> $left['version']);

        $statement = $this->database->prepare("
            DELETE FROM `migrations` WHERE `version`=:version;
        ");

        foreach ($migrationsToMigrate as $migration) {
            echo "Migrate: {$migration['name']}...\n";
            $migrationClass = "Migrations\\{$migration['name']}";

            /** @var Migration $migrationObject */
            $migrationObject = new $migrationClass($this->container, $this->database);
            $migrationObject->down();

            $statement->execute(['version' => $migration['version']]);
        }
    }

    private function createMigrationsTableIfNotExist(): void {
        $database = $this->database->getDatabase();

        $result = $this->database
            ->prepare("
                SELECT count(*) AS cnt
                FROM information_schema.tables
                WHERE table_schema = :database
                AND table_name = 'migrations'
            ")
            ->execute(['database' => $database]);

        $count = (int) $result->fetchSingle();

        if ($count === 0) {
            $this->database->execute("
                CREATE TABLE `migrations` (
                    version INT,
                    name VARCHAR(255),
                    executed_at DATETIME DEFAULT NOW()
                );

                INSERT INTO `migrations` VALUES (0, 'init', NOW());
            ");
        }
    }

    private function getCurrentVersion(): int {
        return (int) $this->database
            ->prepare("SELECT MAX(version) AS current_version FROM `migrations` WHERE 1;")
            ->execute()
            ->fetchSingle();
    }

    private function getLastVersionInFiles(): int {
        return max(array_map(
            fn($migration) => $migration['version'],
            $this->migrations
        ));
    }

    private function loadAvailableMigrations(): void {
        $basePath = ROOT_PATH . '/config/migrations';
        $files = scandir($basePath);
        $files = array_filter($files, fn($path) => str_starts_with($path, 'm_'));
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

    private function loadMigratedMigrations(): void {
        $result = $this->database->prepare("SELECT * FROM `migrations` WHERE 1;")->execute();
        $migrated = $result->fetchAll();

        $this->migrated = [];
        foreach ($migrated as $migration) {
            $this->migrated[$migration['version']] = $migration;
        }
    }
}