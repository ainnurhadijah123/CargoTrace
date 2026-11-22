<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Root endpoint redirects guests to the tracking portal.
     */
    public function test_root_redirects_to_track_portal(): void
    {
        $response = $this->get('/');
        $response->assertRedirect(route('track.index'));
    }

    /**
     * The public tracking portal returns a successful 200 response.
     */
    public function test_tracking_portal_returns_successful_response(): void
    {
        $response = $this->get('/track');
        $response->assertStatus(200);
    }
}
