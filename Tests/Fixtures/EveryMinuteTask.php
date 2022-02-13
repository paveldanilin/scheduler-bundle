<?php

namespace Pada\SchedulerBundle\Tests\Fixtures;

use Pada\SchedulerBundle\Annotation\Scheduled;

/**
 * @Scheduled(cron=Scheduled::EVERY1MIN)
 */
class EveryMinuteTask
{
    public function __invoke(): void
    {
        // DO SOMETHING
    }
}
