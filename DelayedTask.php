<?php

namespace Pada\SchedulerBundle;

final class DelayedTask
{
    private float $timer;
    private Task $task;

    public function __construct(Task $task)
    {
        $this->task = $task;
        $this->timer = \microtime(true);
    }

    public function getTask(): Task
    {
        return $this->task;
    }

    public function getTimer(): float
    {
        return $this->timer;
    }
}
