<?php

namespace Pada\SchedulerBundle\Tests\Fixtures;

use Pada\SchedulerBundle\Annotation\Scheduled;

class EveryHourTask
{
    /**
     * @Scheduled(cron=Scheduled::HOURLY)
     * @return void
     */
    public function doWork(): void
    {
        // DO WORK
    }

    /**
     * @Scheduled(cron=Scheduled::WEEKLY)
     * @return void
     */
    public function doEveryWeek(): void
    {

    }
}
