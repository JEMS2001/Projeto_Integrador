<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
final class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = User::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password = null;

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
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn () => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user should have a verified email.
     */
    public function verified(): static
    {
        return $this->state(fn () => [
            'email_verified_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    /**
     * Set a specific password for the user.
     */
    public function withPassword(string $password): static
    {
        return $this->state(fn () => [
            'password' => Hash::make($password),
        ]);
    }

    /**
     * Create admin user for testing.
     */
    public function admin(): static
    {
        return $this->state(fn () => [
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Configure the factory to handle callbacks.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (User $user) {
            // Log when a user is made for debugging
            Log::debug('User factory: Making user', ['email' => $user->email]);
        })->afterCreating(function (User $user) {
            // Log when a user is created for debugging
            Log::debug('User factory: Created user', ['id' => $user->id, 'email' => $user->email]);
        });
    }
}
