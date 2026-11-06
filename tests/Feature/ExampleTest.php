<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertDontSee('@livewireStyles');
        $response->assertDontSee('@livewireScripts');
        $response->assertSee('Ctrl K');
        $response->assertSee('Danh Mục Nhanh');

        $searchRes = $this->getJson('/api/instant-search?q=pi');
        $searchRes->assertStatus(200);

        $randomRes = $this->get('/ngau-nhien');
        $randomRes->assertStatus(302);
    }
}
