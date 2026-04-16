<?php

namespace Like\Codeception;

use Codeception\Event\FailEvent;
use Codeception\Subscriber\Console as SubscriberConsole;

class Console extends SubscriberConsole {
	public function printFail(FailEvent $event, int $eventNumber): void {
		parent::printFail($event, $eventNumber);
	}
}
