<?php

namespace Pada\SchedulerBundle\Tests;


use Pada\SchedulerBundle\SchedulerBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class TestKernel extends Kernel
{

    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new SchedulerBundle()
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        // TODO: Implement registerContainerConfiguration() method.
    }
}
