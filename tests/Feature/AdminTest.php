<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Repositories\AdminRepository;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_register_successfully(){
        $superAdmin = User::factory()->create([
            'is_super_admin' => 1,
        ]);

        $this->actingAs($superAdmin);


        $data = [
            'username' => 'username',
            'email' => 'user@gmail.com',
            'password' => 'goodpass123',
            'password_confirmation' => 'goodpass123',
        ];

        $data['is_admin'] = 1;

        $response = $this->postJson('/api/register-admin', $data);
        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'email' => 'user@gmail.com',
        ]);
    }


    public function test_super_admin_can_view_all_admins()
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $admins =  User::factory()->count(3)->admin()->create();

        $this->actingAs($superAdmin);

        $response = $this->getJson('/api/view-admins');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                '*' => [
                    'id',
                    'username',
                    'email',
                    'is_admin',
                ],
            ],
        ]);

        $response->assertJsonCount(3, 'data');
    }

    public function test_super_admin_can_view_admin_profile()
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $admin =  User::factory()->admin()->create();

        $this->actingAs($superAdmin, 'sanctum');

        $response = $this->getJson("/api/view-admin-profile/{$admin->id}");

        $response->assertStatus(200);

        $response->assertJson([
            'status' => true,
            'message' => 'Admin profile retrieved successfully',
            'data' => [
                'id' => $admin->id,
                'username' => $admin->username,
                'email' => $admin->email,
                'is_admin' => $admin->is_admin,
            ],
        ]);
    }

    public function test_regular_admin_cannot_view_admin_profile()
    {
        $regularAdmin = User::factory()->admin()->create();

        $admin = User::factory()->admin()->create();

        $this->actingAs($regularAdmin, 'sanctum');

        $response = $this->getJson("/api/view-admin-profile/{$admin->id}");

        $response->assertStatus(403);

        $response->assertJson([
            'status' => false,
            'message' => 'You do not have permission to perform this action.',
        ]);
    }

    public function test_unauthenticated_user_cannot_view_admin_profile()
    {
        $admin = User::factory()->admin()->create();
        
        $response = $this->getJson("/api/view-admin-profile/{$admin->id}");

        $response->assertStatus(401);

        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }
}
