<?php

namespace WatchNext\Engine\Cli;

use WatchNext\Engine\Cli\IO\CliInput;
use WatchNext\Engine\Cli\IO\CliOutput;

interface CliCommandInterface
{
    public function getHelp(): string;

    public function execute(CliInput $input, CliOutput $output): void;
}
