<?php

namespace Pada\SchedulerBundle;

use Psr\Log\LoggerAwareInterface;

interface SchedulerInterface extends LoggerAwareInterface
{
    public function schedule(Task $task): void;
    public function start(): void;
}
