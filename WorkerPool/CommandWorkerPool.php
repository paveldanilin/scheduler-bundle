<?php

namespace Pada\SchedulerBundle\WorkerPool;

use Pada\SchedulerBundle\AbstractTask;
use Pada\SchedulerBundle\CronTask;
use Symfony\Component\Process\PhpExecutableFinder;

final class CommandWorkerPool extends AbstractProcessPool
{
    private string $phpBinaryPath;
    private string $starterScript;
    private array $args;

    public function __construct(string $workDir, string $workerBootstrap, int $maxWorkers, int $maxQueueSize, ?float $taskDelayTimeout = null)
    {
        parent::__construct($maxWorkers, $maxQueueSize, $taskDelayTimeout);
        $this->phpBinaryPath = (new PhpExecutableFinder())->find();
        if (false === $this->phpBinaryPath) {
            throw new \RuntimeException('PHP executable not found');
        }
        $this->starterScript = $this->getStarterScript($workDir, $workerBootstrap);
        $this->args = [];
    }

    public function setArgs(array $args): void
    {
        $this->args = $args;
    }

    protected function buildWorkerCommand(AbstractTask $task): array
    {
        $args = \array_merge($this->args, [$task->getClassName(), $task->getMethodName()]);
        return \array_merge([$this->phpBinaryPath, $this->starterScript], $args);
    }

    public function getStarterScript(string $workDir, string $workerBootstrap): string
    {
        if (\substr($workDir, -1) !== DIRECTORY_SEPARATOR) {
            $workDir .= DIRECTORY_SEPARATOR;
        }
        $bootstrap = $workDir . $workerBootstrap;
        if (!\file_exists($bootstrap)) {
            throw new \RuntimeException('A bootstrap script not found at path "' . $bootstrap . '"');
        }
        return $bootstrap;
    }
}
