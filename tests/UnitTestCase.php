<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * Lightweight base for unit tests that require the Laravel application
 * context (e.g. Eloquent model resolution) but do NOT need a database.
 */
abstract class UnitTestCase extends BaseTestCase
{
    //
}

