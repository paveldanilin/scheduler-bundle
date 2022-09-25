<?php

namespace Pada\SchedulerBundle\Command;

use Pada\SchedulerBundle\CronTask;
use Pada\SchedulerBundle\SchedulerContext;
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

    public function __construct(SchedulerContext $schedulerContext)
    {
        parent::__construct();
        $this->schedulerContext = $schedulerContext;
        $this->logger = new NullLogger();
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
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
                'Scheduler: could not start task. The task class [{class_name}] does not have a method [{method_name}]', [
                    'class_name' => $className,
                    'method_name' => $methodName,
                ]
            );
            throw new \RuntimeException('The task class does not have the ' . $methodName . ' method');
        }

        $taskId = CronTask::generateId($className, $methodName);
        $this->schedulerContext->setCurrentTaskId($taskId);

        $this->logger->debug('[{task_id}] before task.', [
            'task_id' => $taskId,
            'class_name' => $className,
            'method_name' => $methodName,
        ]);

        try {
            \ob_start();
            [$taskServiceObject, $methodName]();
            \ob_get_clean();

            $this->logger->debug('[{task_id}] after task.', [
                'task_id' => $taskId,
                'class_name' => $className,
                'method_name' => $methodName,
            ]);

            $output->writeln("[$taskId] ok");
            return 0;
        } catch (\Throwable $exception) {
            $this->logger->error('[{task_id}] failed. {error}', [
                'task_id' => $taskId,
                'error' => $exception->getMessage(),
            ]);
            throw $exception;
        }
    }
}
