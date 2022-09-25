<?php

namespace Pada\SchedulerBundle;

final class IntervalTask extends AbstractTask
{
    private int $intervalSec;

    public function __construct(string $className, string $methodName, int $intervalSec, ?int $timeout, ?string $errorHandler, int $errorThreshold, ?float $delayTimeout)
    {
        parent::__construct($className, $methodName, $timeout, $errorHandler, $errorThreshold, $delayTimeout);
        $this->intervalSec = $intervalSec;
    }

    public function getInterval(): int
    {
        return $this->intervalSec;
    }
}
