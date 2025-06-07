<?php

namespace Database\Factories;

use App\Models\Membro;
use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class MembroFactory extends Factory
{
    protected $model = Membro::class;

    public function definition(): array
    {
        return [
            'nome' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'cpf' => $this->generateValidCpf(),
            'dataNascimento' => $this->faker->dateTimeBetween('-65 years', '-18 years'),
            'telefone' => $this->faker->optional()->phoneNumber(),
            'empresaId' => null,
            'imagem' => $this->faker->optional()->imageUrl(200, 200, 'people'),
        ];
    }

    /**
     * Create a member associated with a company
     */
    public function withEmpresa(): static
    {
        return $this->state(fn (array $attributes) => [
            'empresaId' => Empresa::factory(),
        ]);
    }

    /**
     * Generate a valid CPF for testing
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
}
