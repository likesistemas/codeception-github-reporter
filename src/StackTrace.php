<?php

namespace Like\Codeception;

use Codeception\Lib\Console\Message;

trait StackTrace {
	public function getExceptionTrace($e) {
		$lines = [];

		if ($this->isSkippedOrIncomplete($e)) {
			return;
		}

		$trace = explode("\n", \PHPUnit\Util\Filter::getFilteredStacktrace($e));

		$i = 0;
		foreach ($trace as $step) {
			$i++;

			$message = $this->message($i)->prepend('#')->width(4);

			if (! isset($step['file'])) {
				foreach (['class', 'type', 'function'] as $info) {
					if (! isset($step[$info])) {
						continue;
					}
					$message->append($step[$info]);
				}
				$lines[] = $message->getMessage();
				continue;
			}
			$message->append($step['file'] . ':' . $step['line']);
			$lines[] = $message->getMessage();
		}

		if (method_exists($e, 'getPrevious')) {
			$prev = $e->getPrevious();
			if ($prev) {
				$lines += $this->getExceptionTrace($prev);
			}
		}

		return $lines;
	}

	private function message($message) {
		return new Message($message);
	}

	private function isSkippedOrIncomplete($e): bool {
		$phpUnit10Skipped = class_exists('PHPUnit\\Event\\Test\\Skipped') && $e instanceof \PHPUnit\Event\Test\Skipped;
		$phpUnit10Incomplete = class_exists('PHPUnit\\Event\\Test\\MarkedIncomplete') && $e instanceof \PHPUnit\Event\Test\MarkedIncomplete;
		$legacySkipped = class_exists('PHPUnit\\Framework\\SkippedTest') && $e instanceof \PHPUnit\Framework\SkippedTest;
		$legacyIncomplete = class_exists('PHPUnit\\Framework\\IncompleteTest') && $e instanceof \PHPUnit\Framework\IncompleteTest;

		return $phpUnit10Skipped || $phpUnit10Incomplete || $legacySkipped || $legacyIncomplete;
	}
}
