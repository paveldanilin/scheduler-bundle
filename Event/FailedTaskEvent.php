<?php

namespace Pada\SchedulerBundle\Event;

class FailedTaskEvent
{
    private string $taskId;
    private string $taskClass;
    private string $taskMethod;
    private \Throwable $throwable;

    public function __construct(string $taskId, string $taskClass, string $taskMethod, \Throwable $throwable)
    {
        $this->taskId = $taskId;
        $this->taskClass = $taskClass;
        $this->taskMethod = $taskMethod;
        $this->throwable = $throwable;
    }

    public function getTaskId(): string
    {
        return $this->taskId;
    }

    public function getTaskClass(): string
    {
        return $this->taskClass;
    }

    public function getTaskMethod(): string
    {
        return $this->taskMethod;
    }

    public function getThrowable(): \Throwable
    {
        return $this->throwable;
    }
}
