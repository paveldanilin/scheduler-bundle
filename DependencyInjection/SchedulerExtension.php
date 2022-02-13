<?php

namespace Pada\SchedulerBundle\DependencyInjection;

use Pada\SchedulerBundle\Task;
use Pada\SchedulerBundle\TaskScannerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;

class SchedulerExtension extends Extension implements CompilerPassInterface
{
    private array $bundleConfig = [];

    public function load(array $configs, ContainerBuilder $container): void
    {
        $this->bundleConfig = $this->processConfiguration(new Configuration(), $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');
    }

    public function process(ContainerBuilder $container): void
    {
        /** @var TaskScannerInterface|null $metaScanner */
        $taskScanner = $container->get('scheduler_bundle_task_scanner');
        if (null === $taskScanner) {
            throw new \RuntimeException('Not found task scanner');
        }

        /** @var Task|null $task */
        foreach ($taskScanner->next() as $task) {
            if (null === $task) {
                break;
            }

            if (!$container->has($task->getClassName())) {
                $container->register($task->getId(), $task->getClassName());
            }

            $definition = $container->findDefinition($task->getId());
            if (!$definition->hasTag('scheduler.task')) {
                $definition->addTag('scheduler.task');
            }
        }
    }
}
