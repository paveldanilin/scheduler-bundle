<?php

namespace Pada\SchedulerBundle;

use Psr\Log\LoggerAwareInterface;

interface SchedulerInterface extends LoggerAwareInterface
{
    public function schedule(AbstractTask $task): void;
    public function start(): void;
}
