<?php

namespace Pada\SchedulerBundle;

final class TaskBuilder implements TaskBuilderInterface
{
    private string $className = '';
    private string $methodName = '';
    private string $cronExpression = '';
    private ?int $timeout = null;
    private ?string $errorHandler = null;
    private int $errorThreshold = -1;
    private ?float $delayedTimeout = null;

    public function className(string $className): TaskBuilderInterface
    {
        $this->className = $className;
        return $this;
    }

    public function methodName(string $methodName): TaskBuilderInterface
    {
        $this->methodName = $methodName;
        return $this;
    }

    public function cron(string $cronExpression): TaskBuilderInterface
    {
        $this->cronExpression = $cronExpression;
        return $this;
    }

    public function timeout(?int $timeout): TaskBuilderInterface
    {
        $this->timeout = $timeout;
        return $this;
    }

    public function errorHandler(?string $errorHandler): TaskBuilderInterface
    {
        $this->errorHandler = $errorHandler;
        return $this;
    }

    public function errorThreshold(int $errorThreshold): TaskBuilderInterface
    {
        $this->errorThreshold = $errorThreshold;
        return $this;
    }

    public function delayedTimeout(?float $delayedTimeout): TaskBuilderInterface
    {
        $this->delayedTimeout = $delayedTimeout;
        return $this;
    }

    private function reset(): void
    {
        $this->className = '';
        $this->methodName = '';
        $this->cronExpression = '';
        $this->timeout = null;
        $this->errorHandler = null;
        $this->errorThreshold = -1;
        $this->delayedTimeout = null;
    }

    public function build(): Task
    {
        $newTask = new Task($this->className,
            $this->methodName,
            $this->cronExpression,
            $this->timeout,
            $this->errorHandler,
            $this->errorThreshold,
            $this->delayedTimeout);
        $this->reset();
        return $newTask;
    }
}
