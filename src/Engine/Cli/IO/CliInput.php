<?php

namespace WatchNext\Engine\Cli\IO;

use Throwable;

class CliInput {
    private string $input;

    public function __construct() {
        global $argv;

        $this->input = implode(' ', $argv);
    }

    public function getOption(string $name): string {
        preg_match('/--' . $name . '=([^ ]+)/', $this->input, $option);

        if (empty($option)) {
            throw new \Exception("There no $name option!");
        }

        return $option[1];
    }

    public function isOptionExist(string $name): bool {
        try {
            $this->getOption($name);
            return true;
        } catch (Throwable $throwable) {
            return false;
        }
    }
}