<?php

namespace Pada\SchedulerBundle;

use Pada\SchedulerBundle\WorkerPool\AbstractWorkerPool;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use React\EventLoop\Loop;

final class Scheduler implements SchedulerInterface
{
    /** @var array<Task> */
    private array $tasks;
    private LoggerInterface $logger;
    private AbstractWorkerPool $workerPool;

    public function __construct(AbstractWorkerPool $workerPool)
    {
        $this->tasks = [];
        $this->logger = new NullLogger();
        $this->workerPool = $workerPool;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function schedule(Task $task): void
    {
        $this->logger->notice('Schedule task id={task_id},cron={cron_expression},timeout={timeout},delayTimeout={delay_timeout},class={class_name},method={method_name}.', [
            'task_id' => $task->getId(),
            'class_name' => $task->getClassName(),
            'method_name' => $task->getMethodName(),
            'cron_expression' => $task->getCronExpression(),
            'timeout' => $task->getTimeout(),
            'delay_timeout' => $task->getDelayTimeout(),
        ]);
        $this->tasks[] = $task;
    }

    public function start(): void
    {
        $this->alignTime();

        Loop::addPeriodicTimer(60, function () {
            foreach ($this->tasks as $task) {
                if ($task->isDue()) {
                    $this->workerPool->start($task);
                }
            }
        });

        Loop::addPeriodicTimer(1, function () {
            $this->workerPool->touch();
        });

        $onStartMessage = 'Scheduled {task_count} task';
        if (\count($this->tasks) > 1) {
            $onStartMessage = 'Scheduled {task_count} tasks';
        }
        $this->logger->notice($onStartMessage, ['task_count' => \count($this->tasks)]);

        foreach ($this->tasks as $scheduledTask) {
            $nextRun = $scheduledTask->updateNextRunDate();
            $this->logger->notice('Task {task_id} next run {next_run}', [
                'task_id' => $scheduledTask->getId(),
                'next_run' => \date('Y-m-d H:i:s', $nextRun),
            ]);
        }

        Loop::run();
    }

    private function alignTime(): void
    {
        $now = (int)(new \DateTimeImmutable())->format('s');
        if ($now > 0) {
            \sleep(60 - $now);
        }
    }
}
