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
     * @param string $taskClass
     * @return mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getTask(string $taskClass)
    {
        return $this->serviceLocator->get($taskClass);
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
