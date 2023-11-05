<?php

namespace WatchNext\Engine\Cli;

use Exception;
use WatchNext\Engine\Cli\IO\CliInput;
use WatchNext\Engine\Cli\IO\CliOutput;
use WatchNext\Engine\Container;
use WatchNext\Engine\Database\Database;
use WatchNext\Engine\Database\Migration;

class MigrationsMigrateCommand implements CliCommandInterface
{
    private array $migrations;
    private array $migrated;

    public function __construct(
        private readonly Container $container,
        private readonly Database $database
    ) {
    }

    public function getHelp(): string
    {
        return 'This command run migrations on selected database
You can specify some version to do or partial up or down grade
For that just add --version=VERSION_NUMBER
';
    }

    public function execute(CliInput $input, CliOutput $output): void
    {
        $output->writeln('Migration of database...');

        $this->loadMigratedMigrations();
        $this->loadAvailableMigrations();

        $currentVersion = $this->getCurrentVersion();
        $selectedVersion = $input->isOptionExist('version')
            ? (int) $input->getOption('version')
            : $this->getLastVersionInFiles();

        if ($currentVersion <= $selectedVersion) {
            $this->up($selectedVersion);
        } else {
            $this->down($selectedVersion);
        }

        $output->writeln('Done');
    }

    private function up(int $selectedVersion): void
    {
        $migrationsToMigrate = array_values(array_filter($this->migrations, function ($migration) use ($selectedVersion) {
            return $migration['version'] <= $selectedVersion && $migration['isMigrated'] === false;
        }));

        usort($migrationsToMigrate, fn ($left, $right) => $left['version'] <=> $right['version']);

        foreach ($migrationsToMigrate as $migration) {
            echo "Migrate: {$migration['name']}...\n";
            $migrationClass = "Migrations\\{$migration['name']}";

            /** @var Migration $migrationObject */
            $migrationObject = new $migrationClass($this->container, $this->database);
            $migrationObject->up();

            $this->database
                ->prepare('
                    INSERT INTO `migration`(`version`, `name`, `executed_at`) VALUES (:version, :name, NOW());
                ')
                ->execute(['version' => $migration['version'], 'name' => $migration['name']]);
        }
    }

    private function down(int $selectedVersion): void
    {
        $migrationsToMigrate = array_values(array_filter($this->migrations, function ($migration) use ($selectedVersion) {
            return $migration['version'] >= $selectedVersion && $migration['isMigrated'] === true;
        }));

        usort($migrationsToMigrate, fn ($left, $right) => $right['version'] <=> $left['version']);

        $statement = $this->database->prepare('
            DELETE FROM `migration` WHERE `version`=:version;
        ');

        foreach ($migrationsToMigrate as $migration) {
            echo "Migrate: {$migration['name']}...\n";
            $migrationClass = "Migrations\\{$migration['name']}";

            /** @var Migration $migrationObject */
            $migrationObject = new $migrationClass($this->container, $this->database);
            $migrationObject->down();

            $statement->execute(['version' => $migration['version']]);
        }
    }

    private function createMigrationsTableIfNotExist(): void
    {
        $database = $this->database->getDatabase();

        $result = $this->database
            ->prepare("
                SELECT count(*) AS cnt
                FROM information_schema.tables
                WHERE table_schema = :database
                AND table_name = 'migration'
            ")
            ->execute(['database' => $database]);

        $count = (int) $result->fetchSingle();

        if ($count === 0) {
            $this->database->execute("
                CREATE TABLE `migration` (
                    version INT,
                    name VARCHAR(255),
                    executed_at DATETIME DEFAULT NOW()
                );

                INSERT INTO `migration` VALUES (0, 'init', NOW());
            ");
        }
    }

    private function getCurrentVersion(): int
    {
        try {
            return (int) $this->database
                ->prepare('SELECT MAX(version) AS current_version FROM `migration` WHERE 1;')
                ->execute()
                ->fetchSingle();
        } catch (Exception $exception) {
            return 0;
        }
    }

    private function getLastVersionInFiles(): int
    {
        return max(array_map(
            fn ($migration) => $migration['version'],
            $this->migrations
        ));
    }

    private function loadAvailableMigrations(): void
    {
        $basePath = ROOT_PATH . '/config/migrations';
        $files = scandir($basePath);
        $files = array_filter($files, fn ($path) => str_starts_with($path, 'm_'));
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

    private function loadMigratedMigrations(): void
    {
        try {
            $result = $this->database->prepare('SELECT * FROM `migration` WHERE 1;')->execute();
            $migrated = $result->fetchAll();
        } catch (Exception $exception) {
            $migrated = [];
        }

        $this->migrated = [];
        foreach ($migrated as $migration) {
            $this->migrated[$migration['version']] = $migration;
        }
    }
}
