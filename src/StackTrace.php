<?php

namespace Like\Codeception;

use Codeception\Lib\Console\Message;

trait StackTrace
{
    public function getExceptionTrace($e)
    {
        $lines = [];

        if ($e instanceof \PHPUnit\Framework\SkippedTestCase or $e instanceof \PHPUnit\Framework\IncompleteTestCase) {
            return;
        }

        $trace = \PHPUnit\Util\Filter::getFilteredStacktrace($e, false);

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
                $lines += $this->printExceptionTrace($prev);
            }
        }

        return $lines;
    }

    private function message($message)
    {
        return new Message($message);
    }
}
