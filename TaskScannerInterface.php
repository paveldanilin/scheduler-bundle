<?php

namespace Pada\SchedulerBundle;

interface TaskScannerInterface
{
    /**
     * @return \Generator<Task>
     */
    public function next(): \Generator;
}
