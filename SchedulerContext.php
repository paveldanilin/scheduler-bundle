<?php

namespace Pada\SchedulerBundle;

use Symfony\Component\DependencyInjection\ServiceLocator;

final class SchedulerContext
{
    private string $currentTaskId;
    private ServiceLocator $serviceLocator;

    public function __construct(ServiceLocator $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        $this->currentTaskId = '';
    }

    /**
     * @param string $taskId (see Task::generateId)
     * @return mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getTask(string $taskId)
    {
        return $this->serviceLocator->get($taskId);
    }

    public function setCurrentTaskId(string $taskId): void
    {
        $this->currentTaskId = $taskId;
    }

    public function getCurrentTaskId(): string
    {
        return $this->currentTaskId;
    }
}
