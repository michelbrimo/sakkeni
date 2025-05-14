<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function createAuthenticatedUser(): array
    {
        $user = User::factory()->create([
            'email' => 'testuser@example.com',
            'password' => bcrypt('Password123!')
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    /** @test */
    public function user_can_update_their_profile_information()
    {
        $auth = $this->createAuthenticatedUser();
        
        $updatedData = [
            'address' => '123 New Street',
            'phone_number' => '0999999999',
            'profile_picture_path' => 'profile.jpg'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $auth['token'],
        ])->postJson('/api/update-profile', $updatedData);

        $response->assertStatus(200)
            ->assertJson([
                "status"=> true,
                "message"=> "User's profile updated successfully",
                "data"=> null
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $auth['user']->id,
            'address' => '123 New Street',
            'phone_number' => '0999999999'
        ]);
    }

    /** @test */
    public function user_cannot_update_profile_with_invalid_data()
    {
        $auth = $this->createAuthenticatedUser();
        
        $invalidData = [
            'phone_number' => 9999,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $auth['token'],
        ])->postJson('/api/update-profile', $invalidData);

        $response->assertStatus(422);
    }

    /** @test */
    public function unauthenticated_user_cannot_update_profile()
    {
        $response = $this->postJson('/api/update-profile', [
            'address' => 'Should not work'
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function user_can_logout_successfully()
    {
        $auth = $this->createAuthenticatedUser();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $auth['token'],
        ])->getJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                "status"=> true,
                "message"=> "User logged out successfully",
                "data"=> null
            ]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $auth['user']->id
        ]);
    }

}