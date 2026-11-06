<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAndRbacTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receives_default_role_and_points(): void
    {
        $response = $this->post(route('register.submit'), [
            'name' => 'Nguyễn Văn Game Thủ',
            'email' => 'nguyenvan@gamehub.local',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();

        $user = User::where('email', 'nguyenvan@gamehub.local')->firstOrFail();
        $this->assertEquals(10, $user->total_points);
        $this->assertTrue($user->hasRole('user'));
    }

    public function test_locked_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'locked@gamehub.local',
            'password' => bcrypt('Password123!'),
            'is_active' => false,
        ]);

        $response = $this->post(route('login.submit'), [
            'email' => $user->email,
            'password' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_standard_user_is_forbidden_from_admin_dashboard(): void
    {
        $userRole = Role::create(['name' => 'Người chơi', 'slug' => 'user']);
        $user = User::factory()->create();
        $user->roles()->attach($userRole->id);

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    public function test_admin_user_can_access_admin_dashboard(): void
    {
        $adminRole = Role::create(['name' => 'Quản trị viên', 'slug' => 'admin']);
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole->id);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Bảng Điều Khiển');
    }
}

