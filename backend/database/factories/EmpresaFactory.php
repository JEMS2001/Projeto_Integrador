<?php

namespace Database\Factories;

use App\Models\Empresa;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class EmpresaFactory extends Factory
{
    protected $model = Empresa::class;

    public function definition(): array
    {
        return [
            'nome' => $this->faker->company(),
            'email' => $this->faker->unique()->safeEmail(),
            'senha' => Hash::make('password'),
            'cnpj' => $this->generateValidCnpj(),
            'endereco' => $this->faker->address(),
            'imagem' => $this->faker->optional()->imageUrl(200, 200, 'business'),
        ];
    }

    /**
     * Generate a valid CNPJ for testing
     */
    private function generateValidCnpj(): string
    {
        // Generate first 12 digits
        $cnpj = '';
        for ($i = 0; $i < 12; $i++) {
            $cnpj .= mt_rand(0, 9);
        }

        // Calculate first verification digit
        $sum = 0;
        $weights = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        for ($i = 0; $i < 12; $i++) {
            $sum += intval($cnpj[$i]) * $weights[$i];
        }
        $firstDigit = 11 - ($sum % 11);
        if ($firstDigit >= 10) $firstDigit = 0;
        $cnpj .= $firstDigit;

        // Calculate second verification digit
        $sum = 0;
        $weights = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        for ($i = 0; $i < 13; $i++) {
            $sum += intval($cnpj[$i]) * $weights[$i];
        }
        $secondDigit = 11 - ($sum % 11);
        if ($secondDigit >= 10) $secondDigit = 0;
        $cnpj .= $secondDigit;

        // Format CNPJ
        return substr($cnpj, 0, 2) . '.' . 
               substr($cnpj, 2, 3) . '.' . 
               substr($cnpj, 5, 3) . '/' . 
               substr($cnpj, 8, 4) . '-' . 
               substr($cnpj, 12, 2);
    }
}
