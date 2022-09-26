<?php

namespace Pada\SchedulerBundle\Tests;

use Pada\SchedulerBundle\AbstractTask;
use Pada\SchedulerBundle\Command\ExecuteCommand;
use Pada\SchedulerBundle\Event\BeforeTaskEvent;
use Pada\SchedulerBundle\Tests\Fixtures\EveryHourTask;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ExecuteCommandTest extends KernelTestCase
{
    public function testCommand(): void
    {
        $kernel = static::createKernel();
        $kernel->boot();
        $app = new Application($kernel);

        /** @var ExecuteCommand $cmd */
        $cmd = $app->find('scheduler:execute');
        $cmdTester = new CommandTester($cmd);
        $cmdTester->execute(['className' => EveryHourTask::class, 'methodName' => 'doWork']);

        static::assertEquals('[dUw9Bj-8TE] ok', \rtrim($cmdTester->getDisplay()));
    }

    public function testEvent(): void
    {
        $kernel = static::createKernel();
        $kernel->boot();
        $app = new Application($kernel);

        $kernel->getContainer()->get('event_dispatcher')->addListener(BeforeTaskEvent::class, static function(BeforeTaskEvent $event) {
            $event->setSkipExecution(true);
        });

        /** @var ExecuteCommand $cmd */
        $cmd = $app->find('scheduler:execute');
        $cmdTester = new CommandTester($cmd);
        $cmdTester->execute(['className' => EveryHourTask::class, 'methodName' => 'doWork']);

        static::assertEquals('[dUw9Bj-8TE] skipped', \rtrim($cmdTester->getDisplay()));
    }
}
