<?php

namespace Pada\SchedulerBundle\Event;

class BeforeTaskEvent
{
    private string $taskId;
    private string $taskClass;
    private string $taskMethod;
    private bool $skipExecution;

    public function __construct(string $taskId, string $taskClass, string $taskMethod)
    {
        $this->taskId = $taskId;
        $this->taskClass = $taskClass;
        $this->taskMethod = $taskMethod;
        $this->skipExecution = false;
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

    public function setSkipExecution(bool $skip): void
    {
        $this->skipExecution = $skip;
    }

    public function getSkipExecution(): bool
    {
        return $this->skipExecution;
    }
}
