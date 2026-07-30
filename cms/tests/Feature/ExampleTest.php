<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_homepage_returns_not_found_when_no_home_page_is_configured(): void
    {
        $response = $this->get('/');

        $response->assertNotFound();
    }
}
