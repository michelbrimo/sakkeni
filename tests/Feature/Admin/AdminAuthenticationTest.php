<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;


    protected function createAdminAndGetToken(array $attributes = []): string
        {
            $user = Admin::factory()->create($attributes);
            return $user->createToken('user-token')->plainTextToken;
        }

    protected function createSuperAdminAndGetToken(array $attributes = []): string
    {
        $user = Admin::factory()->superAdmin()->create($attributes);
        return $user->createToken('user-token')->plainTextToken;
    }


     /** @test */
    public function super_admin_can_register_a_new_admin()
    {
        $token = $this->createSuperAdminAndGetToken();

        $response = $this->withToken($token)->postJson('/api/register-admin', [
            'username' => 'newadmin',
            'email' => 'admin@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
            'phone_number' => '1234567890',
            'address' => 'Admin St.'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('admins', [
            'email' => 'admin@example.com',
            'username' => 'newadmin'
        ]);
    }

    /** @test */
    public function normal_admin_cannot_register_new_admin()
    {
        $token = $this->createAdminAndGetToken();

        $response = $this->withToken($token)->postJson('/api/register-admin', [
            'username' => 'fakeadmin',
            'email' => 'fake@admin.com',
            'password' => 'FakePass123!',
            'password_confirmation' => 'FakePass123!',
            'phone_number' => '1234567890',
            'address' => 'Nowhere'
        ]);

        $response->assertStatus(403); 
    }

    /** @test */
    public function unauthenticated_user_cannot_register_admin()
    {
        $response = $this->postJson('/api/register-admin', [
            'username' => 'unauth',
            'email' => 'unauth@admin.com',
            'password' => 'UnauthPass123!',
            'password_confirmation' => 'UnauthPass123!',
            'phone_number' => '1234567890',
            'address' => 'Nowhere'
        ]);

        $response->assertStatus(401); 
    }

    /** @test */
    public function admin_can_login_with_valid_credentials()
    {
        $admin = Admin::factory()->create([
            'email' => 'admin@login.com',
            'password' => bcrypt('AdminPass123!')
        ]);

        $response = $this->postJson('/api/admin-login', [
            'email' => 'admin@login.com',
            'password' => 'AdminPass123!'
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    // public function admin_cannot_login_with_invalid_credentials()
    // {
    //     $admin = Admin::factory()->create([
    //         'email' => 'admin@login.com',
    //         'password' => bcrypt('RightPass123!')
    //     ]);

    //     $response = $this->postJson('/api/admin-login', [
    //         'email' => 'admin@login.com',
    //         'password' => 'WrongPass!'
    //     ]);

    //     $response->assertStatus(401);
    // }

}