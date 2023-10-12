<?php

return [
    'cache:clear' => \WatchNext\Engine\Cli\CacheClearCommand::class,
    'migrations:generate' => \WatchNext\Engine\Cli\MigrationsGenerateCommand::class,
    'migrations:migrate' => \WatchNext\Engine\Cli\MigrationsMigrateCommand::class,
];