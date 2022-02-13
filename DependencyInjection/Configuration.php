<?php

namespace Pada\SchedulerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('scheduler');

        $treeBuilder->getRootNode()
            ->children()
                // >> task
                ->arrayNode('task')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('dir')->defaultValue('%kernel.project_dir%/src')->end()
                    ->end()
                ->end()
                // << task
            ->end()
        ;

        return $treeBuilder;
    }
}
