<?php

namespace Pada\SchedulerBundle\Tests;

use Pada\SchedulerBundle\Tests\Fixtures\IntervalTask;
use Pada\SchedulerBundle\Tests\Fixtures\EveryHourTask;
use Pada\SchedulerBundle\Tests\Fixtures\EveryMinuteTask;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SchedulerContextTest extends KernelTestCase
{
    public function testEveryMinuteTask(): void
    {
        $kernel = static::createKernel();
        $kernel->boot();

        $task = $kernel->getContainer()->get('scheduler_bundle_context')->getTask(EveryMinuteTask::class);
        self::assertInstanceOf(EveryMinuteTask::class, $task);
    }

    public function testEveryHourTask(): void
    {
        $kernel = static::createKernel();
        $kernel->boot();

        $task = $kernel->getContainer()->get('scheduler_bundle_context')->getTask(EveryHourTask::class);
        self::assertInstanceOf(EveryHourTask::class, $task);
    }

    public function testIntervalTask(): void
    {
        $kernel = static::createKernel();
        $kernel->boot();

        $task = $kernel->getContainer()->get('scheduler_bundle_context')->getTask(IntervalTask::class);
        self::assertInstanceOf(IntervalTask::class, $task);
    }
}
