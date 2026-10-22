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
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertOk();
    }

    public function test_unauthenticated_user_cannot_access_protected_catalog(): void
    {
        $response = $this->get('/katalog');

        $response->assertRedirect('/login');
    }

    public function test_unauthenticated_user_cannot_access_admin_and_is_redirected_to_login(): void
    {
        $response = $this->get('/admin/persetujuan');

        $response->assertRedirect('/login');
    }
}
