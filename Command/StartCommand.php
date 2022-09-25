<?php

namespace Pada\SchedulerBundle\Command;

use Pada\SchedulerBundle\AbstractTask;
use Pada\SchedulerBundle\SchedulerInterface;
use Pada\SchedulerBundle\TaskScannerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class StartCommand extends Command
{
    protected static $defaultName = 'scheduler:start';

    private TaskScannerInterface $taskScanner;
    private SchedulerInterface $scheduler;

    public function __construct(TaskScannerInterface $taskScanner, SchedulerInterface $scheduler)
    {
        parent::__construct();
        $this->taskScanner = $taskScanner;
        $this->scheduler = $scheduler;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var AbstractTask|null $task */
        foreach ($this->taskScanner->next() as $task) {
            if (null === $task) {
                break;
            }
            $this->scheduler->schedule($task);
        }

        $this->scheduler->start();
        return 0;
    }
}
