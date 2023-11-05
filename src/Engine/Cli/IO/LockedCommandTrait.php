<?php

namespace WatchNext\Engine\Cli\IO;

use WatchNext\Engine\Cache\MemcachedCache;

trait LockedCommandTrait
{
    private string $lockPrefix = '';

    public function setLockPrefix(string $lockPrefix): void
    {
        $this->lockPrefix = $lockPrefix;
    }

    public function createLock(int $maxTime = null): void
    {
        $cache = new MemcachedCache();
        $cache->set("app.command.lock.{$this->lockPrefix}" . get_class($this), getmypid(), $maxTime);
    }

    public function isLocked(): bool
    {
        $cache = new MemcachedCache();
        $pid = $cache->read("app.command.lock.{$this->lockPrefix}" . get_class($this));

        if (!$pid) {
            return false;
        }

        return file_exists("/proc/$pid");
    }

    public function unlock(): void
    {
        $cache = new MemcachedCache();
        $cache->delete("app.command.lock.{$this->lockPrefix}" . get_class($this));
    }
}
