<?php

namespace Database\Factories;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Admin::class;

    protected static ?string $password;


    public function configure(): static
    {
        return $this->afterMaking(function (Admin $admin) {
            static::$password ??= Hash::make('password');
        });
    }

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'username' => $this->faker->unique()->userName(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'profile_picture_path' => null,
            'address' => $this->faker->address(),
            'phone_number' => $this->faker->unique()->phoneNumber(),
            'number_of_excepted_houses' => $this->faker->numberBetween(0, 100),
            'is_super_admin' => false,
            'remember_token' => Str::random(10),
        ];
    }

    public function superAdmin(): static
    {
        return $this->state([
            'is_super_admin' => true,
        ]);
    }

    public function admin(): static
    {
        return $this->state([
            'is_super_admin' => false,
        ]);
    }


    public function unverified(): static
    {
        return $this->state([
            'email_verified_at' => null,
        ]);
    }

    public function withProfilePicture(): static
    {
        return $this->state([
            'profile_picture_path' => 'profile_pictures/'.$this->faker->file(
                storage_path('app/public/profile_pictures'),
                storage_path('app/public/profile_pictures'),
                false
            ),
        ]);
    }
}