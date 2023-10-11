<?php

namespace WatchNext\Engine\Cli;

interface CliCommandInterface {
    public function execute(): void;
}