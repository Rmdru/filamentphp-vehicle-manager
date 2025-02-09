<?php

namespace Tests;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $user = User::factory()->create();
        $this->actingAs($user);

        $languages = ['en', 'nl', 'de', 'fr', 'it', 'es'];

        app()->setLocale($languages[array_rand($languages)]);

        Vehicle::factory()->create(['user_id' => auth()->id()]);
    }
}
