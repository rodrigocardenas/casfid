<?php

declare(strict_types=1);

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case instance. This test case is used to set up configuration and state
| for each test.
|
*/

uses(TestCase::class, RefreshDatabase::class, WithFaker::class)->in('Feature');
uses(TestCase::class)->in('Unit');
