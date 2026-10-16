<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\Desa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
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
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => Role::Kader,
            'desa_id' => Desa::factory(),
            'phone' => fake()->phoneNumber(),
            'nik' => fake()->unique()->numerify('################'),
            'alamat' => fake()->address(),
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => Role::Admin,
            'desa_id' => null,
        ]);
    }

    /**
     * Indicate that the user is a bidan desa.
     */
    public function bidan(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => Role::Bidan,
        ]);
    }

    /**
     * Indicate that the user is a kader.
     */
    public function kader(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => Role::Kader,
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
