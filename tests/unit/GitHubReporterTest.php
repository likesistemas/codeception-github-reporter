<?php

namespace Like\Codeception\Tests;

use Like\Codeception\GitHubReporter;
use PHPUnit\Framework\TestCase;

class GitHubReporterTest extends TestCase
{
    public function testInstance()
    {
        $instance = new GitHubReporter([], []);
        $this->assertInstanceOf(GitHubReporter::class, $instance);
    }

    public function testAssertFalse()
    {
        $this->assertTrue(false);
    }

    public function testError()
    {
        $this->assertTrue();
    }
}
