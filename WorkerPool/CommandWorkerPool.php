<?php

namespace Pada\SchedulerBundle\WorkerPool;

use Pada\SchedulerBundle\AbstractTask;
use Symfony\Component\Process\PhpExecutableFinder;

final class CommandWorkerPool extends AbstractProcessPool
{
    private string $phpBinaryPath;
    private string $bootstrapScript;
    private array $args;

    public function __construct(string $workDir, string $workerBootstrap, int $maxWorkers, int $maxQueueSize, ?float $taskDelayTimeout = null)
    {
        parent::__construct($maxWorkers, $maxQueueSize, $taskDelayTimeout);
        $this->phpBinaryPath = (new PhpExecutableFinder())->find();
        if (false === $this->phpBinaryPath) {
            throw new \RuntimeException('PHP executable not found');
        }
        $this->bootstrapScript = $this->getBootstrapScript($workDir, $workerBootstrap);
        $this->args = [];
    }

    /**
     * Sets a command args.
     *
     * @param array $args
     * @return void
     */
    public function setArgs(array $args): void
    {
        $this->args = $args;
    }

    public function getBootstrapScript(string $workDir, string $workerBootstrap): string
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

    /**
     * @class Pada\SchedulerBundle\Command\ExecuteCommand
     *
     * @param AbstractTask $task
     * @return array
     */
    protected function buildWorkerCommand(AbstractTask $task): array
    {
        return \array_merge(
            [$this->phpBinaryPath, $this->bootstrapScript],     // php ./bin/console    <- bin
            \array_merge(
                $this->args,                                    // scheduler:execute    <- command
                [$task->getClassName(), $task->getMethodName()] // <class> <method>     <- A job class-name and method-name
            )
        );
    }
}
