<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'role' => User::ROLE_USER,
            'status_akun' => User::STATUS_AKTIF,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the user is pending approval.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status_akun' => User::STATUS_PENDING,
        ]);
    }

    /**
     * Indicate that the user is rejected.
     */
    public function ditolak(string $alasan = 'Pendaftaran ditolak oleh admin.'): static
    {
        return $this->state(fn (array $attributes) => [
            'status_akun' => User::STATUS_DITOLAK,
            'alasan_penolakan' => $alasan,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return $this
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
