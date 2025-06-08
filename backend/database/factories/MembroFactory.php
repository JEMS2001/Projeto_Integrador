<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Membro;
use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Membro>
 */
final class MembroFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Membro::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password = null;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */    public function definition(): array
    {
        return [
            'nome' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'senha' => 'password', // Model will hash this automatically
            'cpf' => $this->generateValidCpf(),
            'data_nascimento' => fake()->dateTimeBetween('-65 years', '-18 years')->format('Y-m-d'),
            'telefone' => fake()->optional(0.8)->phoneNumber(),
            'empresa_id' => null,
            'imagem' => fake()->optional(0.3)->imageUrl(200, 200, 'people'),
            'ultimo_login' => fake()->optional(0.6)->dateTimeBetween('-30 days', 'now'),
            'termos_aceitos' => true,
        ];
    }

    /**
     * Create a member associated with a company.
     */
    public function withEmpresa(?Empresa $empresa = null): static
    {
        return $this->state(fn (array $_) => [
            'empresa_id' => $empresa?->id ?? Empresa::factory(),
        ]);
    }

    /**
     * Create a member without a company (independent).
     */
    public function independent(): static
    {
        return $this->state(fn (array $_) => [
            'empresa_id' => null,
        ]);
    }    /**
     * Set a specific password for the member.
     */
    public function withPassword(string $password): static
    {
        return $this->state(fn (array $_) => [
            'senha' => $password, // Model will hash this automatically
        ]);
    }

    /**
     * Create a member with recent activity.
     */
    public function active(): static
    {
        return $this->state(fn (array $_) => [
            'ultimo_login' => fake()->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    /**
     * Create a member with no recent activity.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $_) => [
            'ultimo_login' => fake()->optional(0.3)->dateTimeBetween('-6 months', '-1 month'),
        ]);
    }

    /**
     * Create a member with a profile image.
     */
    public function withImage(): static
    {
        return $this->state(fn (array $_) => [
            'imagem' => fake()->imageUrl(200, 200, 'people'),
        ]);
    }

    /**
     * Generate a valid CPF for testing.
     */
    private function generateValidCpf(): string
    {
        // Generate first 9 digits
        $cpf = '';
        for ($i = 0; $i < 9; $i++) {
            $cpf .= mt_rand(0, 9);
        }

        // Calculate first verification digit
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += intval($cpf[$i]) * (10 - $i);
        }
        $firstDigit = 11 - ($sum % 11);
        if ($firstDigit >= 10) $firstDigit = 0;
        $cpf .= $firstDigit;

        // Calculate second verification digit
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += intval($cpf[$i]) * (11 - $i);
        }
        $secondDigit = 11 - ($sum % 11);
        if ($secondDigit >= 10) $secondDigit = 0;
        $cpf .= $secondDigit;

        // Format CPF
        return substr($cpf, 0, 3) . '.' . 
               substr($cpf, 3, 3) . '.' . 
               substr($cpf, 6, 3) . '-' . 
               substr($cpf, 9, 2);
    }

    /**
     * Configure the factory to handle callbacks.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Membro $membro) {
            Log::debug('Membro factory: Making membro', ['email' => $membro->email]);
        })->afterCreating(function (Membro $membro) {
            Log::debug('Membro factory: Created membro', ['id' => $membro->id, 'email' => $membro->email]);
        });
    }
}
