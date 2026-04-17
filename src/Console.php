<?php

namespace Like\Codeception;

use Codeception\Event\FailEvent;
use Codeception\Subscriber\Console as SubscriberConsole;

class Console extends SubscriberConsole {
	public function printFail(FailEvent $event, ?int $eventNumber = null): void {
		$method = new \ReflectionMethod(SubscriberConsole::class, 'printFail');

		$args = [$event];
		if ($method->getNumberOfParameters() > 1 && $eventNumber !== null) {
			$args[] = $eventNumber;
		}

		$method->invokeArgs($this, $args);
	}
}
