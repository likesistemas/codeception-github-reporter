<?php

namespace Like\Codeception;

use Codeception\Event\FailEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Extension;
use Codeception\Test\Descriptor;
use Codeception\Test\Interfaces\ScenarioDriven;
use Codeception\TestInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use InvalidArgumentException;

class GitHubReporter extends Extension
{

    /** @var array */
    private $lang;

    /** @var string */
    private $currentTest = '';

    /** @var string */
    private $currentTestSuit;

    /** @var array */
    private $tests = [];

    /** @var array */
    private $errors = [];

    /** @var Console */
    protected $standardReporter;

    public function _initialize()
    {
        $this->options['silent'] = false; // turn on printing for this extension
        $this->_reconfigure(['settings' => ['silent' => true]]); // turn off printing for everything else
        $this->standardReporter = new Console($this->options);
        $this->lang = Lang::getLang(getenv('TEST_LANG'));
    }

    // we are listening for events
    public static $events = [
        Events::SUITE_BEFORE => 'beforeSuite',
        Events::SUITE_AFTER => 'afterSuite',
        Events::TEST_END => 'after',
        Events::TEST_SUCCESS => 'success',
        Events::TEST_FAIL => 'fail',
        Events::TEST_ERROR => 'error',
        Events::TEST_FAIL_PRINT => 'printFailed',
        Events::RESULT_PRINT_AFTER => 'result',
    ];

    protected function _write($message)
    {
        $this->currentTest .= $message;
    }

    protected function _writeln($message)
    {
        $this->currentTest .= $message;

        if (strlen($this->currentTest) > 0) {
            $this->tests[$this->currentTestSuit][] = $this->currentTest;
            $this->currentTest = '';
        }
    }

    public function beforeSuite($e)
    {
        $this->currentTestSuit = ucfirst($e->getSuite()->getName()) . " Tests ({$e->getSuite()->count()})";
        $this->standardReporter->beforeSuite($e);
    }

    public function afterSuite($e)
    {
        $this->standardReporter->afterSuite($e);
    }

    public function success($e)
    {
        $this->_write(':heavy_check_mark: ');
        $this->standardReporter->testSuccess($e);
    }

    public function fail($e)
    {
        $this->_write(':x: ');
        $this->standardReporter->testFail($e);
    }

    public function error($e)
    {
        $this->_write(':o: ');
        $this->standardReporter->testError($e);
    }

    public function skipped()
    {
        $this->_write('- [ ] :white_circle: ');
        $this->standardReporter->testSkipped($e);
    }

    public function after(TestEvent $e)
    {
        $seconds_input = $e->getTime();
        $seconds = (int) ($milliseconds = (int) ($seconds_input * 1000)) / 1000;
        $time = ($seconds % 60).(($milliseconds === 0) ? '' : '.'.$milliseconds);

        $this->_write(Descriptor::getTestSignature($e->getTest()));
        $this->_writeln(' ('.$time.'s)');

        $this->standardReporter->endTest($e);
    }

    public function printFailed(FailEvent $e)
    {
        $failedTest = $e->getTest();
        $fail = $e->getFail();

        $error = [];
        $error[] = $e->getCount().') '.Descriptor::getTestAsString($failedTest);
        $error[] = Descriptor::getTestFullName($failedTest);
        if ($fail->getMessage()) {
            $error[] = $fail->getMessage();
        }

        if ($failedTest instanceof ScenarioDriven) {
            $reports = $this->getReports($failedTest);
            if ($reports) {
                var_dump($reports);
                $error[] = $reports;
            }
        } else {
            $trace = $this->getExceptionTrace($fail);
            if ($trace) {
                $error += $trace;
            }
        }

        $this->errors[] = $error;

        $this->standardReporter->printFail($e);
    }

    use StackTrace;

    private function getReports(TestInterface $failedTest)
    {
        $reports = $failedTest->getMetadata()->getReports();
        if (isset($reports['png'])) {
            $token = getenv('TOKEN_IMGBB');
            
            try {
                if (! $token) {
                    throw new InvalidArgumentException('To upload it informs the environment variable `TOKEN_IMGBB`.');
                }

                $upload = new UploadImage($token);
                $url = $upload->upload($reports['png']);
                return sprintf($this->lang['image'], $url);
            } catch (RequestException $ex) {
                $this->writeln("Error on upload image: {$ex->getMessage()}");
            } catch (InvalidArgumentException $ex) {
                $this->writeln($ex->getMessage());
            }
        }
    }

    public function result()
    {
        $TITLE = getenv('TEST_TITLE');
        $FOOTER = (getenv('TEST_FOOTER') ?: 'true') === 'true';
        $OWNER = getenv('GITHUB_OWNER');
        $REPO = getenv('GITHUB_REPO');
        $PR_NUMBER = getenv('GITHUB_PR_NUMBER');
        $TOKEN = getenv('GITHUB_TOKEN');

        $body = $this->getBodyMessage($this->lang, $TITLE, $FOOTER);
        $this->writeln('');
        $this->writeln($body);

        try {
            if (! $OWNER) {
                throw new InvalidArgumentException('It is necessary to send the environment variable `GITHUB_OWNER`.');
            }

            if (! $REPO) {
                throw new InvalidArgumentException('It is necessary to send the environment variable `GITHUB_REPO`.');
            }

            if (! $PR_NUMBER) {
                throw new InvalidArgumentException('It is necessary to send the environment variable `GITHUB_PR_NUMBER`.');
            }

            if (! $TOKEN) {
                throw new InvalidArgumentException('It is necessary to send the environment variable `GITHUB_TOKEN`.');
            }

            $URL = "https://api.github.com/repos/{$OWNER}/{$REPO}/issues/${PR_NUMBER}/comments";

            $client = new Client();
            $client->request('POST', $URL, [
                'body' => json_encode(['body' => $body]),
                'auth' => ['token', $TOKEN],
            ]);
        } catch (RequestException $ex) {
            $this->writeln("Error on comment in github: {$ex->getMessage()}");
        } catch (InvalidArgumentException $ex) {
            $this->writeln("Error on comment in github: {$ex->getMessage()}");
        }
    }

    private function getBodyMessage(array $lang, $title = null, $footer = true)
    {
        $message = '';

        if ($title) {
            $message .= "**$title**\n";
        }

        if (count($this->errors) == 0) {
            $message .= "{$lang['success']}\n";
        } else {
            $message .= sprintf($lang['fail'], count($this->errors))."\n\n";
            foreach ($this->errors as $error) {
                $message .= "```\n".join("\n", $error)."\n```\n";
            }
            $message .= "\n\n";
        }

        foreach ($this->tests as $suiteName => $suiteTests) {
            $message .= '<details>';
            $message .= "<summary>{$suiteName}</summary>\n";
            $message .= '<p>' . join('<br>', $suiteTests) . '</p>';
            $message .= '</details>';
        }

        if ($footer) {
            $message .= "\n\n<sup>*" . sprintf($lang['footer'], 'PHP v' . phpversion()) . '*</sup>';
        }

        return $message;
    }
}
