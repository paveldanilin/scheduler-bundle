<?php

namespace Pada\SchedulerBundle\Logger;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

abstract class LoggerFactory
{
    public static function createLogger(): LoggerInterface
    {
        $logger = new Logger('scheduler');
        $rotatingHandler = new RotatingFileHandler('scheduler.log', 30, Logger::DEBUG);
        $logger->pushHandler($rotatingHandler);
        return $logger;
    }
}
