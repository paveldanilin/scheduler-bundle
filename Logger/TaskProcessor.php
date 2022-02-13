<?php

namespace Pada\SchedulerBundle\Logger;

use Pada\SchedulerBundle\SchedulerContext;

class TaskProcessor
{
    private SchedulerContext $schedulerContext;

    public function __construct(SchedulerContext $schedulerContext)
    {
        $this->schedulerContext = $schedulerContext;
    }

    public function __invoke(array $record): array
    {
        $taskId = $this->schedulerContext->getCurrentTaskId();
        if (!empty($taskId)) {
            $record['task_id'] = $taskId;
        }
        return $record;
    }
}
