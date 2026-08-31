<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_root_renders_the_landing_page(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertViewIs('landing');
        $response->assertViewHas('plans');
        $response->assertViewHas('stats');
    }

    public function test_legal_pages_render(): void
    {
        $this->get('/termos')->assertOk();
        $this->get('/privacidade')->assertOk();
    }
}
