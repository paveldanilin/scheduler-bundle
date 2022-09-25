<?php

namespace Pada\SchedulerBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;


/**
 * @Annotation
 * @Annotation\Target({"CLASS", "METHOD"})
 */
class Scheduled
{
    public const EVERY1MIN = '@min';
    public const EVERY5MIN = '@5min';
    public const EVERY10MIN = '@10min';
    public const EVERY15MIN = '@15min';
    public const EVERY20MIN = '@20min';
    public const EVERY30MIN = '@30min';
    public const HOURLY = '@hourly';
    public const DAILY = '@daily';
    public const WEEKLY = '@weekly';
    public const MONTHLY = '@monthly';
    public const YEARLY = '@yearly';

    private ?int $interval; // sec
    private ?string $cron;
    private ?int $timeout;
    private ?float $delayTimeout;
    private ?string $errorHandler;
    private int $errorThreshold;

    public function __construct(array $data)
    {
        $this->cron = $data['cron'] ?? null;
        $this->interval = $data['interval'] ?? null;
        $this->timeout = $data['timeout'] ?? null;
        $this->delayTimeout = $data['delayTimeout'] ?? null;
        $this->errorHandler = $data['errorHandler'] ?? null;
        $this->errorThreshold = $data['errorThreshold'] ?? 0;

        if (null === $this->interval && empty($this->cron)) {
            throw new \RuntimeException('`interval` or `cron` property must be defined');
        }

        if (null !== $this->interval && $this->interval <= 0) {
            throw new \RuntimeException('`interval` must be > 0');
        }
    }

    public function getCron(): ?string
    {
        return $this->cron;
    }

    public function getInterval(): ?int
    {
        return $this->interval;
    }

    /**
     * Returns timeout in seconds.
     * null if there is no timeout.
     *
     * @return int|null
     */
    public function getTimeout(): ?int
    {
        return $this->timeout;
    }

    public function getDelayTimeout(): ?float
    {
        return $this->delayTimeout;
    }

    public function getErrorHandler(): ?string
    {
        return $this->errorHandler;
    }

    public function getErrorThreshold(): int
    {
        return $this->errorThreshold;
    }
}
