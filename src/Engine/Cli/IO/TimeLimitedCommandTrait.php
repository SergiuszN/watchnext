<?php

namespace WatchNext\Engine\Cli\IO;

trait TimeLimitedCommandTrait
{
    private float $started;
    private int $maxExecutionTime;

    public function start(int $maxExecutionTime): void
    {
        $this->started = microtime(true);
        $this->maxExecutionTime = $maxExecutionTime;
    }

    public function isEnd(): bool
    {
        return ($this->started + $this->maxExecutionTime) < microtime(true);
    }
}
