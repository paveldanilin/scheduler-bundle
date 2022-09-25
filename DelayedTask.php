<?php

namespace Pada\SchedulerBundle;

final class DelayedTask
{
    private float $timer;
    private AbstractTask $task;

    public function __construct(AbstractTask $task)
    {
        $this->task = $task;
        $this->timer = \microtime(true);
    }

    public function getTask(): AbstractTask
    {
        return $this->task;
    }

    public function getTimer(): float
    {
        return $this->timer;
    }
}
