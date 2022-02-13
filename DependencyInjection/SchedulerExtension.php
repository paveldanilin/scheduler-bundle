<?php

namespace Pada\SchedulerBundle\DependencyInjection;

use Pada\SchedulerBundle\Task;
use Pada\SchedulerBundle\TaskScannerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Reference;

class SchedulerExtension extends Extension implements CompilerPassInterface
{
    private array $bundleConfig = [];

    public function load(array $configs, ContainerBuilder $container): void
    {
        $this->bundleConfig = $this->processConfiguration(new Configuration(), $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');


        $container->getDefinition('scheduler_bundle_worker_pool')
            ->addMethodCall('setLogger', [new Reference('logger')]);

        $container->getDefinition('scheduler_bundle_scheduler')
            ->addMethodCall('setLogger', [new Reference('logger')]);
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

            if (!$container->hasDefinition($task->getClassName())) {
                $container->register($task->getClassName(), $task->getClassName());
            }

            $definition = $container->findDefinition($task->getClassName());
            if (!$definition->hasTag('scheduler.task')) {
                $definition->addTag('scheduler.task');
            }
        }
    }
}
