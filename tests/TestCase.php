<?php

namespace Aiglos\Lba\Tests;

use Aiglos\Lba\LbaServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            LbaServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Set up config if needed
        $_SERVER['HTTP_HOST'] = 'localhost';
    }
}