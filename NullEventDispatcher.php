<?php

namespace Pada\SchedulerBundle;

use Psr\EventDispatcher\EventDispatcherInterface;

final class NullEventDispatcher implements EventDispatcherInterface
{
    public function dispatch(object $event)
    {
        return $event;
    }
}
