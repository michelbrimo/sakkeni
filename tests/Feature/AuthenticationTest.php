<?php

namespace Tests\Feature;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;



class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    function register_user($username="username", $email="user@gmail.com", $password="goodpass@123"){
        $registerData = [
            'username' => $username,
            'email' => $email,
            'password' => $password,
        ];
        $userObj = new UserRepository();
        $user = $userObj->create($registerData);
        return $user->createToken('personal access token')->plainTextToken;
    }

    public function test_register_a_new_user_successfully()
    {
        $data = [
            'username' => 'username',
            'email' => 'user@gmail.com',
            'password' => 'goodpass123',
            'password_confirmation' => 'goodpass123',
        ];

        $response = $this->postJson('/api/sign-up', $data);
        $response->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'email' => 'user@gmail.com',
        ]);
    }

    public function test_register_duplicated_user()
    {
        $registerData = [
            'username' => 'username',
            'email' => 'user@gmail.com',
            'password' => 'goodpass123',
            'password_confirmation' => 'goodpass123',
        ];
        $this->register_user(email:$registerData['email']);

        $response = $this->postJson('/api/sign-up', $registerData);
        $response->assertStatus(500);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_register_missing_credentials()
    {
        $registerData = [
            'email' => 'user@gmail.com',
            'password' => 'goodpass123',
            'password_confirmation' => 'goodpass123',
        ];
        
        $response = $this->postJson('/api/sign-up', $registerData);
        $response->assertStatus(422);

        $this->assertDatabaseMissing('users', [
            'email' => 'user@gmail.com',
        ]);
    }
    
    public function test_login_successfully()
    {
        $loginData = [
            'email' => 'user@gmail.com',
            'password' => 'goodpass123',
        ];
        $this->register_user(email:$loginData['email'], password:$loginData['password']);

        $response = $this->postJson('/api/login', $loginData);
        $response->assertStatus(200);
    }

    public function test_login_incorrect_credentials()
    {
        $this->register_user(email:"user@gmail.com");
        $loginData = [
            'email' => 'wrong@email.com',
            'password' => 'goodpass123',
        ];

        $response = $this->postJson('/api/login', $loginData);
        $response->assertStatus(400);
    }

    public function test_view_my_profile_successfully()
    {
        $token = $this->register_user();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get('/api/my-profile');

        $response->assertStatus(200);
    }

    public function test_update_my_profile()
    {
        $token = $this->register_user();
        $updatedData = [
            'address' => 'New Address',
            'phoneNumber' => '0999999999',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/update-profile', $updatedData);
        $response->assertStatus(200);
        
        $user = User::where('email', '=', 'user@gmail.com')->first();

        $this->assertNotNull($user);
        $this->assertEquals('New Address', $user->address);
        $this->assertEquals('0999999999', $user->phoneNumber);
    }

    public function test_logout_successfully()
    {
        $token = $this->register_user();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->get('/api/logout');

        $response->assertStatus(200);
    }
}

