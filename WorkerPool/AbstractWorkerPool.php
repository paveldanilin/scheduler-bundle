<?php

namespace Pada\SchedulerBundle\WorkerPool;

use Pada\SchedulerBundle\AbstractTask;
use Pada\SchedulerBundle\DelayedTask;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class AbstractWorkerPool implements LoggerAwareInterface
{
    protected const LOG_PREFIX = 'SCHEDULER';

    private int $maxWorkers;
    private int $maxQueueSize;
    private ?float $taskDelayTimeout;
    private \SplQueue $queue;
    private LoggerInterface $logger;

    public function __construct(int $maxWorkers, int $maxQueueSize, ?float $taskDelayTimeout)
    {
        $this->maxQueueSize = $maxQueueSize;
        $this->maxWorkers = $maxWorkers;
        $this->taskDelayTimeout = $taskDelayTimeout;
        $this->queue = new \SplQueue();
        $this->logger = new NullLogger();
    }

    abstract public function touch(): void;
    abstract protected function doStart(int $workerId, AbstractTask $task): void;
    abstract protected function getAvailableWorkerId(): ?int;

    public function getMaxWorkers(): int
    {
        return $this->maxWorkers;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    protected function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    public function start(AbstractTask $task): void
    {
        $workerId = $this->getAvailableWorkerId();
        if (null === $workerId) {
            $this->enqueue($task);
            return;
        }
        $this->doStart($workerId, $task);
    }

    protected function pushQueueProcess(): void
    {
        if (0 === $this->queue->count()) {
            $this->logger->debug('[{prefix}] No queued tasks, nothing to execute.', [
                'prefix' => self::LOG_PREFIX
            ]);
            return;
        }

        $task = $this->dequeue();
        if (null === $task) {
            $this->logger->warning('[{prefix}] Dequeued an empty task, nothing to execute.', [
                'prefix' => self::LOG_PREFIX
            ]);
            return;
        }

        $workerId = $this->getAvailableWorkerId();
        if (null === $workerId) {
            $this->logger->warning('[{prefix}] Cannot process a queue, all workers are busy.', [
                'prefix' => self::LOG_PREFIX
            ]);
            $this->enqueue($task);
            return;
        }

        $this->doStart($workerId, $task);
    }

    private function enqueue(AbstractTask $task): void
    {
        if ($this->maxQueueSize <= 0) {
            $this->logger->error('[{prefix}] [{task_id}] could not enqueue a scheduled task, queuing is disabled.', [
                'prefix' => self::LOG_PREFIX,
                'task_id' => $task->getId(),
            ]);
            return;
        }
        if ($this->queue->count() > $this->maxQueueSize) {
            $this->logger->error('[{prefix}] [{task_id}] could not enqueue a scheduled task, run out of a queue capacity.', [
                'prefix' => self::LOG_PREFIX,
                'task_id' => $task->getId(),
            ]);
            return;
        }
        $this->queue->enqueue(new DelayedTask($task));
        $this->logger->debug('[{prefix}] [{task_id}]] scheduled task has been put to a queue.', [
            'prefix' => self::LOG_PREFIX,
            'queue_size' => $this->queue->count(),
            'task_id' => $task->getId(),
        ]);
    }

    private function dequeue(): ?AbstractTask
    {
        $now = \microtime(true);
        $task = null;
        while (true) {
            /** @var DelayedTask|null $delayedTask */
            $delayedTask = $this->queue->pop();
            if (null === $delayedTask) {
                break;
            }
            $delayTimeout = $delayedTask->getTask()->getDelayTimeout() ?? $this->taskDelayTimeout ?? -1;
            if ($delayTimeout > 0 && ($now - $delayedTask->getTimer()) >= $delayTimeout) {
                $this->logger->warning('[{prefix}] Delayed task [{task_id}] is expired', [
                    'prefix' => self::LOG_PREFIX,
                    'task_id' => $delayedTask->getTask()->getId()
                ]);
                continue;
            }
            $task = $delayedTask->getTask();
            break;
        }
        return $task;
    }
}
