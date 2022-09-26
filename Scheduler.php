<?php

namespace Pada\SchedulerBundle;

use Pada\SchedulerBundle\WorkerPool\AbstractWorkerPool;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use React\EventLoop\Loop;

final class Scheduler implements SchedulerInterface
{
    /** @var array<AbstractTask> */
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

    public function schedule(AbstractTask $task): void
    {
        $this->logger->notice(
            'New task id={task_id},cron={cron_expression},timeout={timeout},delayTimeout={delay_timeout},class={class_name},method={method_name}.',
            $this->buildLogContext($task)
        );
        $this->tasks[] = $task;
    }

    public function start(): void
    {
        $this->startIntervalTasks();
        $this->startCronTasks();

        $this->logger->notice('[{task_count}] tasks', [
            'task_count' => \count($this->tasks)
        ]);

        foreach ($this->tasks as $scheduledTask) {
            $nextRun = $scheduledTask->updateNextRunDate();
            $this->logger->notice('Task {task_id} next run {next_run}', [
                'task_id' => $scheduledTask->getId(),
                'next_run' => \date('Y-m-d H:i:s', $nextRun),
            ]);
        }

        // WorkerPool supervisor
        Loop::addPeriodicTimer(1, function () {
            $this->workerPool->touch();
        });

        Loop::run();
    }

    private function startIntervalTasks(): void
    {
        /** @var IntervalTask[] $intervalTasks */
        $intervalTasks = \array_filter($this->tasks, static fn(AbstractTask $task) => $task instanceof IntervalTask);
        if (0 === \count($intervalTasks)) {
            return;
        }

        // Grouped by interval
        $grouped = [];
        foreach ($intervalTasks as $intervalTask) {
            if (!\array_key_exists($intervalTask->getInterval(), $grouped)) {
                $grouped[$intervalTask->getInterval()] = [];
            }
            $grouped[$intervalTask->getInterval()][] = $intervalTask;
        }

        /**
         * @var int $intervalSec
         * @var IntervalTask[] $tasks
         */
        foreach ($grouped as $intervalSec => $tasks) {
            foreach ($tasks as $task) {
                // TODO: inject timer object into task object
                Loop::addPeriodicTimer($intervalSec, fn() => $this->workerPool->start($task));
            }
        }
    }

    private function startCronTasks(): void
    {
        /** @var CronTask[] $cronTasks */
        $cronTasks = \array_filter($this->tasks, static fn (AbstractTask $task) => $task instanceof CronTask);
        if (0 === \count($cronTasks)) {
            return;
        }

        $this->alignTime();

        // Tick - check - start
        Loop::addPeriodicTimer(60, function () use($cronTasks) {
            foreach ($cronTasks as $task) {
                if ($task->isDue()) {
                    $this->workerPool->start($task);
                }
            }
        });
    }

    /**
     * Sleeps up to the next 0-second
     * @return void
     */
    private function alignTime(): void
    {
        $now = (int)(new \DateTimeImmutable())->format('s');
        if ($now > 0) {
            \sleep(60 - $now);
        }
    }

    private function buildLogContext(AbstractTask $task): array
    {
        $logContext = [
            'task_id' => $task->getId(),
            'class_name' => $task->getClassName(),
            'method_name' => $task->getMethodName(),
            'timeout' => $task->getTimeout(),
            'delay_timeout' => $task->getDelayTimeout(),
        ];
        if ($task instanceof CronTask) {
            $logContext['cron_expression'] = $task->getCronExpression();
        }
        if ($task instanceof IntervalTask) {
            $logContext['interval'] = $task->getInterval();
        }
        return $logContext;
    }
}
