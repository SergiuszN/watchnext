<?php

namespace WatchNext\Engine\Cli;

class GenerateMigrationCommand implements CliCommandInterface {
    public function execute(): void {
        echo "Migrate!\n";
    }
}