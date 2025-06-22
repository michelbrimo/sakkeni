<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;

use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;


    protected function createAuthenticatedAdmin(): array
    {
        $admin = Admin::factory()->create([
            'email' => 'testadmin@example.com',
            'password' => bcrypt('Password123!')
        ]);

        $token = $admin->createToken('test-token')->plainTextToken;

        return ['user' => $admin, 'token' => $token];
    }

    protected function createAuthenticatedSuperAdmin(): array
    {
        $superAdmin = Admin::factory()->create([
            'email' => 'testsuperadmin@example.com',
            'password' => bcrypt('Password123!')
        ]);

        $token = $superAdmin->createToken('test-token')->plainTextToken;

        return ['user' => $superAdmin, 'token' => $token];
    }

    public function test_super_admin_can_view_all_admins()
    {
        $superAdmin = Admin::factory()->superAdmin()->create();

        $admins =  Admin::factory()->count(3)->admin()->create();

        $this->actingAs($superAdmin);

        $response = $this->getJson('/api/view-admins');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                '*' => [
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                ],
            ],
        ]);

        $response->assertJsonCount(4, 'data');
    }

    // public function test_super_admin_can_view_admin_profile()
    // {
    //     $superAdmin = $this->createAuthenticatedSuperAdmin();

    //     $admin =  $this->createAuthenticatedAdmin();

    //     $token = $superAdmin['token']; 

    //     $response = $this->withToken($token)->getJson("/api/view-admin-profile/{$admin['user']->id}");

    //     $response->assertStatus(200);

    //     $response->assertJson([
    //         'status' => true,
    //         'message' => "Admin's profile fetched successfully",
    //         'data' => [
    //             'id' => $admin['user']->id,
    //             'first_name' => $admin['user']->first_name,
    //             'last_name' => $admin['user']->last_name,
    //             'email' => $admin['user']->email,
    //         ],
    //     ]);
    // }

    public function test_regular_admin_cannot_view_admin_profile()
    {
        $regularAdmin = Admin::factory()->admin()->create();

        $admin = Admin::factory()->admin()->create();

        $this->actingAs($regularAdmin, 'sanctum');

        $response = $this->getJson("/api/view-admin-profile/{$admin->id}");

        $response->assertStatus(401);

        $response->assertJson([
            "message"=> "Unauthenticated."
        ]);
    }

    public function test_unauthenticated_user_cannot_view_admin_profile()
    {
        $admin = Admin::factory()->admin()->create();
        
        $response = $this->getJson("/api/view-admin-profile/{$admin->id}");

        $response->assertStatus(401);

        $response->assertJson([
            'message' => 'Unauthenticated.',
        ]);
    }


    // public function test_super_admin_can_delete_admin()
    // {
    //     $superAdmin = $this->createAuthenticatedSuperAdmin();

    //     $admin =  $this->createAuthenticatedAdmin();

    //     $token = $superAdmin['token']; 

    //     $response = $this->withToken($token)->deleteJson("/api/remove-admin/{$admin['user']->id}");

    //     $response->assertStatus(200)
    //         ->assertJson([
    //             'status' => true,
    //             'message' => 'Admin removed successfully'
    //         ]);

    //     $this->assertNull(Admin::find($admin['user']->id));
    // }

    public function test_regular_admin_cannot_delete_admin()
    {
        $regularAdmin = Admin::factory()->admin()->create();
        $admin = Admin::factory()->admin()->create();

        $this->actingAs($regularAdmin, 'sanctum');

        $response = $this->deleteJson("/api/remove-admin/{$admin->id}");

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    // public function test_cannot_delete_nonexistent_admin()
    // {
    //     $superAdmin = Admin::factory()->superAdmin()->create();
    //     $this->actingAs($superAdmin, 'sanctum');

    //     $response = $this->deleteJson("/api/remove-admin/999");

    //     $response->assertStatus(404)
    //         ->assertJson([
    //             'status' => false,
    //             'message' => 'Admin not found'
    //         ]);
    // }

    /** @test */
    public function admin_can_logout_successfully()
    {
        $auth = $this->createAuthenticatedAdmin();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $auth['token'],
        ])->getJson('/api/logout-admin');

        $response->assertStatus(200)
            ->assertJson([
                "status"=> true,
                "message"=> "admin logged out successfully",
                "data"=> null
            ]);

        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $auth['user']->id
        ]);
    }

    /** @test */
    //  public function test_can_search_admins_by_name()
    // {
    //     $superAdmin = Admin::factory()->superAdmin()->create();
    //     $token = $superAdmin->createToken('admin-token')->plainTextToken;

    //     Admin::factory()->create(['first_name' => 'john_doe', 'last_name' => 'lastName']);
    //     Admin::factory()->create(['first_name' => 'jane_doe', 'last_name' => 'lastName']);
    //     Admin::factory()->create(['first_name' => 'bob_smith', 'last_name' => 'lastName']);

    //     $response = $this->withHeaders([
    //         'Authorization' => 'Bearer ' . $token
    //     ])->postJson('/search-admin', ['name' => 'doe']);

    //     $response->assertStatus(200)
    //         ->assertJson([
    //             'status' => true,
    //             'message' => "Admins fetched successfully"
    //         ])
    //         ->assertJsonCount(2, 'data');
    // }


}
