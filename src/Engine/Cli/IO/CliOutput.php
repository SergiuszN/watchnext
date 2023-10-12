<?php

namespace WatchNext\Engine\Cli\IO;

class CliOutput {
    public function write(string $message): void {
        echo $message;
    }

    public function writeln(string $message): void {
        echo $message;
        echo "\n";
    }
}