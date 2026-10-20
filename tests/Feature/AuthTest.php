<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_user_can_login_using_username_and_password(): void
    {
        $user = User::factory()->create([
            'username' => 'guru_test',
            'email' => 'guru@test.com',
            'password' => Hash::make('password123'),
            'role' => 'guru_mapel',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'login' => 'guru_test',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect('/dashboard');
    }

    public function test_user_cannot_login_with_invalid_password_blackbox_scenario_1(): void
    {
        $user = User::factory()->create([
            'username' => 'admin_test',
            'password' => Hash::make('correct_password'),
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'login' => 'admin_test',
            'password' => 'wrong_password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('login');
    }

    public function test_inactive_user_cannot_login_blackbox_scenario_2(): void
    {
        $user = User::factory()->create([
            'username' => 'nonaktif_user',
            'password' => Hash::make('password123'),
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'login' => 'nonaktif_user',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['login' => 'Akun Anda berstatus nonaktif. Silakan hubungi Administrator.']);
    }

    public function test_unauthenticated_user_accessing_dashboard_redirects_to_login_blackbox_scenario_4(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_user_can_logout_and_session_is_invalidated(): void
    {
        $user = User::factory()->create([
            'role' => 'siswa',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }
}
