<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    protected static ?string $password;


    public function configure(): static
    {
        return $this->afterMaking(function (User $user) {
            static::$password ??= Hash::make('password');
        });
    }


    public function definition(): array
    {
        return [
            'first_name' => $this->faker->unique()->firstName(),
            'last_name' => $this->faker->unique()->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'profile_picture_path' => null,
            'address' => $this->faker->address(),
            'phone_number' => $this->faker->unique()->phoneNumber(),
            'remember_token' => Str::random(10),
        ];
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


    public function withAttributes(array $attributes): static
    {
        return $this->state($attributes);
    }
}