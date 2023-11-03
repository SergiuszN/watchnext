<?php

namespace WatchNext\Engine\Cli\IO;

class CliInput
{
    private string $input;

    /** @var string[] */
    private array $argv;

    public function __construct()
    {
        global $argv;

        $this->argv = $argv;
        $this->input = implode(' ', $argv);
    }

    public function getArgument(int $index, string $default = null, bool $required = false, string $requiredError = ''): ?string
    {
        if ($required) {
            if (!isset($this->argv[$index + 2])) {
                echo $requiredError . "\n";
                exit;
            }
        }

        return $this->argv[$index + 2] ?? $default;
    }

    public function getOption(string $name, bool $required = false, string $default = null): ?string
    {
        $option = [];
        preg_match('/--(' . $name . ')=?([^ ]+)?/', $this->input, $option);

        if ($required && !isset($option[1])) {
            echo "You must provide required '$name' option!\n";
            exit;
        }

        return $option[2] ?? $default;
    }

    public function isOptionExist(string $name): bool
    {
        $option = [];
        preg_match('/--(' . $name . ')=?([^ ]+)?/', $this->input, $option);

        return isset($option[1]);
    }

    public function setArgv(array $argv): void
    {
        $this->argv = $argv;
        $this->input = implode(' ', $argv);
    }
}
