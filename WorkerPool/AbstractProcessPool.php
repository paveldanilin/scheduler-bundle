<?php

namespace Pada\SchedulerBundle\WorkerPool;

use Pada\SchedulerBundle\AbstractTask;
use Pada\SchedulerBundle\CronTask;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

abstract class AbstractProcessPool extends AbstractWorkerPool
{
    /** @var array<Process> */
    private array $workers;

    /**
     * @var array<string> [WorkerId] = TaskId
     */
    private array $workerTaskMap;


    public function __construct(int $maxWorkers, int $maxQueueSize, ?float $taskDelayTimeout)
    {
        parent::__construct($maxWorkers, $maxQueueSize, $taskDelayTimeout);
        $this->workers = [];
        for ($i = 0; $i < $maxWorkers; $i++) {
            $this->workers[] = new Process([]);
        }
        $this->workerTaskMap = [];
    }

    abstract protected function buildWorkerCommand(AbstractTask $task): array;

    protected function doStart(int $workerId, AbstractTask $task): void
    {
        $this->getLogger()->debug('[{task_id}-{worker_id}] starting task...', [
            'task_id' => $task->getId(),
            'worker_id' => $workerId,
            'class_name' => $task->getClassName(),
            'method_name' => $task->getMethodName(),
        ]);

        $this->workers[$workerId] = new Process($this->buildWorkerCommand($task));
        $this->workers[$workerId]->setTimeout($task->getTimeout());
        $this->workers[$workerId]->disableOutput();
        $this->workerTaskMap[$workerId] = $task->getId();
        try {
            $this->workers[$workerId]->start(fn($type, $output) => $this->handle($type, $output, $workerId, $task));
        } catch (\Exception $exception) {
            unset($this->workerTaskMap[$workerId]);
            $this->getLogger()->error('[{task_id}-{worker_id}] failed to start task. {error}', [
                'error' => $exception->getMessage(),
                'task_id' => $task->getId(),
                'worker_id' => $workerId,
            ]);
        }
    }

    private function handle(string $type, string $output, int $workerId, AbstractTask $task): void
    {
        unset($this->workerTaskMap[$workerId]);
        if (Process::ERR === $type) {
            $this->onTaskError($workerId, $task, $output);
        } else {
            $this->onTaskSuccess($workerId, $task);
        }
        $this->pushQueueProcess();
    }

    protected function getAvailableWorkerId(): ?int
    {
        foreach ($this->workers as $workerId => $process) {
            if (!$process->isRunning()) {
                return $workerId;
            }
        }
        return null;
    }

    public function touch(): void
    {
        foreach ($this->workers as $workerId => $process) {
            try {
                if ($process->isRunning()) {
                    $process->checkTimeout();
                }
            } catch (ProcessTimedOutException $exception) {
                $taskId = $this->workerTaskMap[$workerId] ?? -1;
                unset($this->workerTaskMap[$workerId]);
                $this->getLogger()->error('[{task_id}-{worker_id}] task has failed with timeout.', [
                    'task_id' => $taskId,
                    'worker_id' => $workerId,
                    'error' => $exception->getMessage()
                ]);
            }
        }
    }

    private function onTaskSuccess(int $workerId, AbstractTask $task): void
    {
        $task->addSuccess();
        $logContext = [
            'success_count' => $task->getSuccessCount(),
            'task_id' => $task->getId(),
            'worker_id' => $workerId,
        ];
        if ($task instanceof CronTask) {
            $logContext['next_run'] = \date('Y-m-d H:i:s', $task->getNextRunDate());
        }
        $this->getLogger()->info(
            '[{task_id}-{worker_id}] a task completed, count={success_count}, next={next_run}',
            $logContext
        );
    }

    private function onTaskError(int $workerId, AbstractTask $task, string $processOutput): void
    {
        $task->addError();

        $this->getLogger()->error('[{task_id}-{worker_id}] task failed, count={error_count}.', [
            'task_id' => $task->getId(),
            'worker_id' => $workerId,
            'error_count' => $task->getErrorCount(),
            'process_output' => $processOutput,
            'class_name' => $task->getClassName(),
            'method_name' => $task->getMethodName(),
        ]);

        if ($task->getErrorThreshold() > 0 && $task->getErrorCount() > $task->getErrorThreshold()) {
            $errorHandler = $task->getErrorHandler();
            if (null === $errorHandler) {
                $task->disable();
                $this->getLogger()->alert(
                    '[{task_id}-{worker_id}] error count exceeded the threshold ({error_count} > {error_threshold}), going to disable task.',
                    [
                        'task_id' => $task->getId(),
                        'worker_id' => $workerId,
                        'error_threshold' => $task->getErrorThreshold(),
                        'error_count' => $task->getErrorCount(),
                        'class_name' => $task->getClassName(),
                        'method_name' => $task->getMethodName(),
                    ]
                );
            } else {
                $errorHandlerCallable = [$task->getClassName(), $errorHandler];
                $errorHandlerCallable($task);
            }
        }
    }
}
