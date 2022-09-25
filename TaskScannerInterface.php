<?php

namespace Pada\SchedulerBundle;

interface TaskScannerInterface
{
    /**
     * @return \Generator<AbstractTask>
     */
    public function next(): \Generator;
}
