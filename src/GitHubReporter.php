<?php

namespace Like\Codeception;

use Codeception\Event\FailEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Extension;
use Codeception\Test\Descriptor;
use GuzzleHttp\Client;
use Codeception\Subscriber\Console;
use GuzzleHttp\Exception\RequestException;
use InvalidArgumentException;

class GitHubReporter extends Extension {

    /** @var string */
    private $line = "";

    /** @var array */
    private $tests = [];

    /** @var array */
    private $errors = [];
    
    /** @var Console */
    protected $standardReporter;

    public function _initialize() {
        $this->options['silent'] = false; // turn on printing for this extension
        $this->_reconfigure(['settings' => ['silent' => true]]); // turn off printing for everything else
        $this->standardReporter = new Console($this->options);
    }

    // we are listening for events
    public static $events = [
        Events::SUITE_BEFORE => 'beforeSuite',
        Events::TEST_END     => 'after',
        Events::TEST_SUCCESS => 'success',
        Events::TEST_FAIL    => 'fail',
        Events::TEST_ERROR   => 'error',
        Events::TEST_FAIL_PRINT => 'printFailed',
        Events::RESULT_PRINT_AFTER => 'result'
    ];

    protected function write($message) {
        parent::write($message);
        $this->line .= $message;
    }

    protected function writeln($message) {
        parent::writeln($message);
        $this->line .= $message;

        if( strlen($this->line) > 0 ) {
            $this->tests[] = $this->line;
            $this->line = "";
        }
    }

    public function beforeSuite() {
        $this->writeln("");
    }

    public function success() {
        $this->write(':heavy_check_mark: ');
    }

    public function fail() {
        $this->write(':x: ');
    }

    public function error() {
        $this->write(':o: ');
    }

    public function skipped() {
        $this->write(':white_circle: ');
    }

    public function after(TestEvent $e) {
        $seconds_input = $e->getTime();
        $seconds = (int)($milliseconds = (int)($seconds_input * 1000)) / 1000;
        $time = ($seconds % 60) . (($milliseconds === 0) ? '' : '.' . $milliseconds);

        $this->write(Descriptor::getTestSignature($e->getTest()));
        $this->writeln(' (' . $time . 's)');
    }

    public function printFailed(FailEvent $e) {
        $failedTest = $e->getTest();
        $fail = $e->getFail();

        $error = [];
        $error[] = $e->getCount() . ") " . Descriptor::getTestAsString($failedTest);
        $error[] = Descriptor::getTestFullName($failedTest);
        $error[] = $fail->getMessage();
        $this->errors[] = $error;
    }

    public function result() {
        $OWNER = getenv("GITHUB_OWNER");
        $REPO = getenv("GITHUB_REPO");
        $PR_NUMBER = getenv("GITHUB_PR_NUMBER");
        $TOKEN = getenv("GITHUB_TOKEN");

        try {
            if(!$OWNER) {
                throw new InvalidArgumentException("É necessário enviar a variável de ambiente `GITHUB_OWNER`.");
            }

            if(!$REPO) {
                throw new InvalidArgumentException("É necessário enviar a variável de ambiente `GITHUB_REPO`.");
            }

            if(!$PR_NUMBER) {
                throw new InvalidArgumentException("É necessário enviar a variável de ambiente `GITHUB_PR_NUMBER`.");
            }

            if(!$TOKEN) {
                throw new InvalidArgumentException("É necessário enviar a variável de ambiente `GITHUB_TOKEN`.");
            }

            $URL = "https://api.github.com/repos/{$OWNER}/{$REPO}/issues/${PR_NUMBER}/comments";

            $client = new Client();
            $client->request('POST', $URL, [
                'body' => json_encode(['body' => $this->getBodyMessage()]),
                'auth' => ['token', $TOKEN]
            ]);
        } catch(InvalidArgumentException | RequestException $ex) {
            $this->writeln("Error on comment in github: {$ex->getMessage()}");
        }
    }

    private function getBodyMessage() {
        if( count($this->errors) == 0 ) {
            $message = ":smiley: Parabéns, seus testes passaram...\n";
        } else {
            $message  = ":boom: Testes falharam, acontecerem " . count($this->errors) . " erros: \n\n";
            foreach($this->errors as $error) {
                $message .= "```\n" . join("\n", $error) . "\n```\n";
            }
            $message .= "\n\n";
        }

        $message .= join("\n", $this->tests);

        return $message;
    }

}