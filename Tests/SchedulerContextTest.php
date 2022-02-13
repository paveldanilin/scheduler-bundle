<?php

namespace Pada\SchedulerBundle\Tests;

use Pada\SchedulerBundle\Task;
use Pada\SchedulerBundle\Tests\Fixtures\EveryHourTask;
use Pada\SchedulerBundle\Tests\Fixtures\EveryMinuteTask;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SchedulerContextTest extends KernelTestCase
{
    public function testClassInvoke(): void
    {
        $kernel = static::createKernel();
        $kernel->boot();
        $task = $kernel->getContainer()->get('scheduler_bundle_context')->getTask(Task::generateId(EveryMinuteTask::class, '__invoke'));
        self::assertInstanceOf(EveryMinuteTask::class, $task);
    }

    public function testMethod(): void
    {
        $kernel = static::createKernel();
        $kernel->boot();

        $task = $kernel->getContainer()->get('scheduler_bundle_context')->getTask(Task::generateId(EveryHourTask::class, 'doWork'));
        self::assertInstanceOf(EveryHourTask::class, $task);

        $task = $kernel->getContainer()->get('scheduler_bundle_context')->getTask(Task::generateId(EveryHourTask::class, 'doEveryWeek'));
        self::assertInstanceOf(EveryHourTask::class, $task);
    }
}
