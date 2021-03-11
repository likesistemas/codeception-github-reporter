<?php

namespace Like\Codeception;

use Codeception\Event\FailEvent;
use Codeception\Subscriber\Console as SubscriberConsole;

class Console extends SubscriberConsole
{
    public function printFail(FailEvent $e)
    {
        parent::printFail($e);
    }

    
}
