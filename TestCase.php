<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Simula la función base_path para los tests.
     */
    protected function setUp(): void
    {
        parent::setUp();

        if (!function_exists('base_path')) {
            function base_path($path = '')
            {
                return realpath(__DIR__ . '/../..') . ($path ? DIRECTORY_SEPARATOR . $path : '');
            }
        }
    }
}
