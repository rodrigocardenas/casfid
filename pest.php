<?php

/*
|--------------------------------------------------------------------------
| Pest Configuration
|--------------------------------------------------------------------------
|
| By default, Pest will look for tests in the tests/ directory and the
| tests are discovered from the src directory. However, you can change
| this configuration as needed.
|
*/

use Pest\TestSuite;

TestSuite::group('feature')->in('tests/Feature');
TestSuite::group('unit')->in('tests/Unit');
