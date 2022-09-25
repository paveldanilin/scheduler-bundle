<?php

namespace Pada\SchedulerBundle;

interface TaskBuilderInterface
{
    public function className(string $className): self;
    public function methodName(string $methodName): self;
    public function cron(string $cronExpression): self;
    public function interval(int $interval): self;
    public function timeout(?int $timeout): self;
    public function errorHandler(?string $errorHandler): self;
    public function errorThreshold(int $errorThreshold): self;
    public function delayedTimeout(?float $delayedTimeout): self;
    public function build(): AbstractTask;
}
