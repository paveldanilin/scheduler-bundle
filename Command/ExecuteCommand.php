<?php

namespace Pada\SchedulerBundle\Command;

use Pada\SchedulerBundle\AbstractTask;
use Pada\SchedulerBundle\Event\AfterTaskEvent;
use Pada\SchedulerBundle\Event\BeforeTaskEvent;
use Pada\SchedulerBundle\Event\FailedTaskEvent;
use Pada\SchedulerBundle\NullEventDispatcher;
use Pada\SchedulerBundle\SchedulerContext;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class ExecuteCommand extends Command
{
    protected static $defaultName = 'scheduler:execute';

    private LoggerInterface $logger;
    private SchedulerContext $schedulerContext;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(SchedulerContext $schedulerContext)
    {
        parent::__construct();
        $this->schedulerContext = $schedulerContext;
        $this->logger = new NullLogger();
        $this->eventDispatcher = new NullEventDispatcher();
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function setEventDispatcher(?EventDispatcherInterface $eventDispatcher): void
    {
        $this->eventDispatcher = $eventDispatcher ?? new NullEventDispatcher();
    }

    protected function configure(): void
    {
        $this->addArgument('className', InputArgument::REQUIRED, 'A task class');
        $this->addArgument('methodName', InputArgument::REQUIRED, 'A method name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $className = (string)$input->getArgument('className');
        $methodName = (string)$input->getArgument('methodName');

        $taskServiceObject = $this->schedulerContext->getTask($className);
        if (!\method_exists($taskServiceObject, $methodName)) {
            $this->logger->error(
                'A method does not exist `{class_name}::{method_name}`', [
                    'class_name' => $className,
                    'method_name' => $methodName,
                ]
            );
            throw new \RuntimeException('A method does not exist `' . $className.'::'.$methodName . '`');
        }

        $taskId = AbstractTask::generateId($className, $methodName);
        $this->schedulerContext->setCurrentTaskId($taskId);

        // Before task
        $mustSkip = $this->beforeTask($taskId, $className, $methodName);
        if ($mustSkip) {
            $output->writeln("[$taskId] skipped");
            return 0;
        }
        // Call task
        $taskResult = $this->callTask($taskId, $taskServiceObject, $className, $methodName);
        // After task
        $this->afterTask($taskId, $className, $methodName, $taskResult);

        $output->writeln("[$taskId] ok");
        return 0;
    }

    /**
     * Returns TRUE if execution MUST be skipped.
     * Returns FALSE if execution MUST NOT be skipped.
     *
     * @param string $taskId
     * @param string $className
     * @param string $methodName
     * @return bool
     * @throws \Throwable
     */
    private function beforeTask(string $taskId, string $className, string $methodName): bool
    {
        $this->logger->debug('[{task_id}] [BEFORE] `{class_name}::{method_name}`.', [
            'task_id' => $taskId,
            'class_name' => $className,
            'method_name' => $methodName,
        ]);

        try {
            $event = $this->eventDispatcher->dispatch(new BeforeTaskEvent($taskId, $className, $methodName));
            if ($event instanceof BeforeTaskEvent) {
                return $event->getSkipExecution();
            }
            return false;
        } catch (\Throwable $throwable) {
            $this->logger->error('[{task_id}] [FAILED] `{class_name}::{method_name}`: {error}.', [
                'task_id' => $taskId,
                'class_name' => $className,
                'method_name' => $methodName,
                'error' => $throwable->getMessage(),
            ]);
            $this->eventDispatcher->dispatch(new FailedTaskEvent($taskId, $className, $methodName, $throwable));
            throw $throwable;
        }
    }

    /**
     * @param string $taskId
     * @param mixed $taskServiceObject
     * @param string $className
     * @param string $methodName
     * @return mixed
     * @throws \Throwable
     */
    private function callTask(string $taskId, $taskServiceObject, string $className, string $methodName)
    {
        try {
            \ob_start();
            $ret = [$taskServiceObject, $methodName]();
            \ob_get_clean();
            return $ret;
        } catch (\Throwable $throwable) {
            $this->logger->error('[{task_id}] [FAILED] `{class_name}::{method_name}`: {error}.', [
                'task_id' => $taskId,
                'class_name' => $className,
                'method_name' => $methodName,
                'error' => $throwable->getMessage(),
            ]);
            $this->eventDispatcher->dispatch(new FailedTaskEvent($taskId, $className, $methodName, $throwable));
            throw $throwable;
        }
    }

    /**
     * @param string $taskId
     * @param string $className
     * @param string $methodName
     * @param mixed $taskResult
     * @return void
     * @throws \Throwable
     */
    private function afterTask(string $taskId, string $className, string $methodName, $taskResult): void
    {
        $this->logger->debug('[{task_id}] [AFTER] `{class_name}::{method_name}`.', [
            'task_id' => $taskId,
            'class_name' => $className,
            'method_name' => $methodName,
        ]);

        try {
            $this->eventDispatcher->dispatch(new AfterTaskEvent($taskId, $className, $methodName, $taskResult));
        } catch (\Throwable $throwable) {
            $this->logger->error('[{task_id}] [FAILED] `{class_name}::{method_name}`: {error}.', [
                'task_id' => $taskId,
                'class_name' => $className,
                'method_name' => $methodName,
                'error' => $throwable->getMessage(),
            ]);
            $this->eventDispatcher->dispatch(new FailedTaskEvent($taskId, $className, $methodName, $throwable));
            throw $throwable;
        }
    }
}
