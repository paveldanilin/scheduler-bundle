<?php

namespace Pada\SchedulerBundle;

abstract class AbstractTask
{
    private string $id;
    private string $className;
    private string $methodName;
    private ?int $timeout;
    private ?float $delayTimeout;
    private int $errorCount;
    private int $successCount;
    private bool $enabled;
    private ?string $errorHandler;
    private int $errorThreshold;

    public function __construct(string $className, string $methodName, ?int $timeout, ?string $errorHandler, int $errorThreshold, ?float $delayTimeout)
    {
        $this->id = self::generateId($className, $methodName);
        $this->className = $className;
        $this->methodName = $methodName;
        $this->timeout = $timeout;
        $this->delayTimeout = $delayTimeout;
        $this->errorCount = 0;
        $this->successCount = 0;
        $this->enabled = true;
        $this->errorHandler = $errorHandler;
        $this->errorThreshold = $errorThreshold;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }

    public function getTimeout(): ?int
    {
        return $this->timeout;
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

    public function getDelayTimeout(): ?float
    {
        return $this->delayTimeout;
    }

    public static function generateId(string $className, string $methodName): string
    {
        $length = 10;
        $hashBase64 = \base64_encode( \hash( 'sha256', ($className.$methodName) , true ) );
        $hashUrlsafe = \strtr( $hashBase64, '+/', '-_' );
        $hashUrlsafe = \rtrim( $hashUrlsafe, '=' );
        return \substr( $hashUrlsafe, 0, $length );
    }
}
