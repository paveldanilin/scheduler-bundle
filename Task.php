<?php

namespace Pada\SchedulerBundle;

use Cron\CronExpression;
use Pada\SchedulerBundle\Annotation\Scheduled;

final class Task
{
    private string $id;
    private CronExpression $cronExpression;
    private string $className;
    private string $methodName;
    private ?int $timeout;
    private int $nextRun;
    private int $errorCount;
    private int $successCount;
    private bool $enabled;
    private ?string $errorHandler;
    private int $errorThreshold;
    private ?float $delayTimeout;

    public function __construct(string $className, string $methodName, string $cron, ?int $timeout, ?string $errorHandler, int $errorThreshold, ?float $delayTimeout = null)
    {
        $this->id = self::generateId($className, $methodName);
        $this->cronExpression = self::createCronExpression($cron);
        $this->updateNextRunDate();
        $this->className = $className;
        $this->methodName = $methodName;
        $this->timeout = $timeout;
        $this->errorCount = 0;
        $this->successCount = 0;
        $this->enabled = true;
        $this->errorHandler = $errorHandler;
        $this->errorThreshold = $errorThreshold;
        $this->delayTimeout = $delayTimeout;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function isDue(): bool
    {
        if ($this->enabled && $this->nextRun <= (new \DateTime())->getTimestamp()) {
            $this->updateNextRunDate();
            return true;
        }
        return false;
    }

    public function getNextRunDate(): int
    {
        return $this->nextRun;
    }

    public function getTimeout(): ?int
    {
        return $this->timeout;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    public function addError(): void
    {
        $this->errorCount++;
    }

    public function addSuccess(): void
    {
        $this->successCount++;
    }

    public function getErrorCount(): int
    {
        return $this->errorCount;
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function enable(): void
    {
        $this->enabled = true;
    }

    public function disable(): void
    {
        $this->enabled = false;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getErrorHandler(): ?string
    {
        return $this->errorHandler;
    }

    public function getErrorThreshold(): int
    {
        return $this->errorThreshold;
    }

    public function getCronExpression(): string
    {
        return $this->cronExpression->getExpression();
    }

    public function getDelayTimeout(): ?float
    {
        return $this->delayTimeout;
    }

    public function updateNextRunDate(): int
    {
        $this->nextRun = $this->cronExpression->getNextRunDate()->getTimestamp();
        return $this->nextRun;
    }

    public static function generateId(string $className, string $methodName): string
    {
        $length = 10;
        $hashBase64 = \base64_encode( \hash( 'sha256', ($className.$methodName) , true ) );
        $hashUrlsafe = \strtr( $hashBase64, '+/', '-_' );
        $hashUrlsafe = \rtrim( $hashUrlsafe, '=' );
        return \substr( $hashUrlsafe, 0, $length );
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
