<?php

namespace Pada\SchedulerBundle;

use Cron\CronExpression;
use Pada\SchedulerBundle\Annotation\Scheduled;

final class CronTask extends AbstractTask
{
    private CronExpression $cronExpression;
    private int $nextRun;

    public function __construct(string $className, string $methodName, string $cron, ?int $timeout, ?string $errorHandler, int $errorThreshold, ?float $delayTimeout = null)
    {
        parent::__construct($className, $methodName, $timeout, $errorHandler, $errorThreshold, $delayTimeout);
        $this->cronExpression = self::createCronExpression($cron);
        $this->updateNextRunDate();
    }

    public function isDue(): bool
    {
        if ($this->isEnabled() && $this->nextRun <= (new \DateTime())->getTimestamp()) {
            $this->updateNextRunDate();
            return true;
        }
        return false;
    }

    public function getNextRunDate(): int
    {
        return $this->nextRun;
    }

    public function getCronExpression(): string
    {
        return $this->cronExpression->getExpression();
    }

    public function updateNextRunDate(): int
    {
        $this->nextRun = $this->cronExpression->getNextRunDate()->getTimestamp();
        return $this->nextRun;
    }

    private static function createCronExpression(string $cron): CronExpression
    {
        $cron = \str_replace('\\', '', self::resolveCronMacros($cron));
        if (!CronExpression::isValidExpression($cron)) {
            throw new \InvalidArgumentException('Bad cron expression [' . $cron . ']');
        }
        return new CronExpression($cron);
    }

    private static function resolveCronMacros(string $cron): string
    {
        $macros = [
            Scheduled::EVERY1MIN => '* * * * *',
            Scheduled::EVERY5MIN => '*/5 * * * *',
            Scheduled::EVERY10MIN => '*/10 * * * *',
            Scheduled::EVERY15MIN => '*/15 * * * *',
            Scheduled::EVERY20MIN => '*/20 * * * *',
            Scheduled::EVERY30MIN => '*/30 * * * *',
            Scheduled::HOURLY => '0 * * * *',
            Scheduled::DAILY => '0 0 * * *',
            Scheduled::WEEKLY => '0 0 * * 1',
            Scheduled::MONTHLY => '0 0 1 * *',
            Scheduled::YEARLY => '0 0 1 1 *',
        ];
        return $macros[$cron] ?? $cron;
    }
}
