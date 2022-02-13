<?php

namespace Pada\SchedulerBundle\Tests;

use Pada\SchedulerBundle\Command\ExecuteCommand;
use Pada\SchedulerBundle\Task;
use Pada\SchedulerBundle\Tests\Fixtures\EveryHourTask;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ExecuteCommandTest extends KernelTestCase
{
    public function testCommand(): void
    {
        $kernel = static::createKernel();
        $app = new Application($kernel);

        /** @var ExecuteCommand $cmd */
        $cmd = $app->find('scheduler:execute');
        $cmdTester = new CommandTester($cmd);
        $cmdTester->execute(['className' => EveryHourTask::class, 'methodName' => 'doWork']);

        static::assertEquals('[dUw9Bj-8TE] ok','[' . Task::generateId(EveryHourTask::class, 'doWork') . '] ok');
    }
}
