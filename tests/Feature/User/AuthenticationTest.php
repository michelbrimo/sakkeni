<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function createUserAndGetToken(array $attributes = []): string
    {
        $user = User::factory()->create($attributes);
        return $user->createToken('user-token')->plainTextToken;
    }

    /** @test */
    public function user_can_register_with_valid_credentials()
    {
        $response = $this->postJson('/api/sign-up', [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'username' => 'testuser'
        ]);
    }

    /** @test */
    public function user_cannot_register_with_existing_email()
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->postJson('/api/sign-up', [
            'username' => 'newuser',
            'email' => 'existing@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!'
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function user_can_login_with_correct_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('Password123!')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'Password123!'
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function user_cannot_login_with_invalid_credentials()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('Password123!')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'WrongPassword!'
        ]);

        $response->assertStatus(400);
    }

    /** @test */
    public function authenticated_user_can_view_their_profile()
    {
        $token = $this->createUserAndGetToken();

        $response = $this->withToken($token)
            ->getJson('/api/my-profile');

        $response->assertStatus(200);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_protected_routes()
    {
        $response = $this->getJson('/api/my-profile');
        $response->assertStatus(401);
    }
}