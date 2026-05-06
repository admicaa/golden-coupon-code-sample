<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithAdminAuth;
use Tests\TestCase;

class AdminAuthTest extends TestCase
{
    use InteractsWithAdminAuth;
    use RefreshDatabase;

    public function test_invalid_admin_login_returns_unauthorized()
    {
        Admin::create([
            'name' => 'Login Admin',
            'email' => 'login@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->postJson('/api/login/admin', [
            'email' => 'login@example.com',
            'password' => 'wrongpass',
        ]);

        $response->assertStatus(401);
    }

    public function test_admin_login_request_validates_payload()
    {
        $response = $this->postJson('/api/login/admin', [
            'email' => 'not-an-email',
            'password' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_authenticated_admin_user_endpoint_returns_current_admin()
    {
        $admin = $this->actingAsAdminWithPermissions([]);

        $response = $this->getJson('/api/admin/user');

        $response->assertOk()
            ->assertJsonPath('id', $admin->id)
            ->assertJsonPath('email', $admin->email);
    }
}
