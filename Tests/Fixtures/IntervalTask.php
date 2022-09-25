<?php

namespace Pada\SchedulerBundle\Tests\Fixtures;

use Pada\SchedulerBundle\Annotation\Scheduled;

/**
 * @Scheduled(interval=15)
 */
class IntervalTask
{
    public function __invoke(): void
    {
        // DO SOMETHING
    }
}
