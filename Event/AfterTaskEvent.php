<?php

namespace Pada\SchedulerBundle\Event;

class AfterTaskEvent
{
    private string $taskId;
    private string $taskClass;
    private string $taskMethod;
    /**
     * A result of task execution.
     * @var mixed
     */
    private $taskResult;

    public function __construct(string $taskId, string $taskClass, string $taskMethod, $taskResult)
    {
        $this->taskId = $taskId;
        $this->taskClass = $taskClass;
        $this->taskMethod = $taskMethod;
        $this->taskResult = $taskResult;
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

    /**
     * @return mixed
     */
    public function getTaskResult()
    {
        return $this->taskResult;
    }
}
