## CRON
- @yearly, @annually - Run once a year, midnight, Jan. 1 - 0 0 1 1 *
- @monthly - Run once a month, midnight, first of month - 0 0 1 * *
- @weekly - Run once a week, midnight on Sun - 0 0 * * 0
- @daily, @midnight - Run once a day, midnight - 0 0 * * *
- @hourly - Run once an hour, first minute - 0 * * * *
- @min - Run every minute - * * * * *

```php
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
```

## Interval (sec)

```php
use Pada\SchedulerBundle\Annotation\Scheduled;

/**
 * @Scheduled(interval=30)
 */
class EveryMinuteTask
{
    public function __invoke(): void
    {
        // DO SOMETHING EVERY 30 SEC
    }
}
```

## Start scheduler
```shell
php ./bin/console scheduler:start
```
