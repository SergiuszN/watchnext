<?php

namespace WatchNext\Engine\Cli;

interface CliCommandInterface
{
    public function getHelp(): string;

    public function execute(): void;
}
