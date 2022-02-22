<?php

namespace Pada\SchedulerBundle\Logger;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

abstract class LoggerFactory
{
    public static function createLogger(string $logDir): LoggerInterface
    {
        $logger = new Logger('scheduler');
        $rotatingHandler = new RotatingFileHandler($logDir . DIRECTORY_SEPARATOR . 'scheduler.log', 30, Logger::DEBUG);
        $logger->pushHandler($rotatingHandler);
        return $logger;
    }
}
