<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->name();

        return [
            'f_name' => $name,
            'email' => fake()->unique()->safeEmail(),
            'nomor_telepon' => fake()->numerify('08##########'),
            'email_verified_at' => now(),
            'username' => Str::slug($name) . '-' . fake()->unique()->numberBetween(100, 999),
            'password' => static::$password ??= Hash::make('password'),
            'user' => 'User',
            'img' => 'default-avatar.png',
            'alamat' => fake()->address(),
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn () => [
            'f_name' => 'Admin ' . fake()->firstName(),
            'username' => 'admin-' . fake()->unique()->numberBetween(100, 999),
            'user' => 'Admin',
        ]);
    }

    public function customer(): static
    {
        return $this->state(fn () => [
            'user' => 'User',
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
