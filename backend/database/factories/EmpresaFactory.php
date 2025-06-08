<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Empresa;
use App\Models\Membro;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Empresa>
 */
final class EmpresaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Empresa::class;

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
        $companyName = fake()->company();
        return [
            'nome' => $companyName,
            'nome_fantasia' => $companyName . ' ' . fake()->companySuffix(),
            'razao_social' => $companyName . ' Ltda',
            'email' => fake()->unique()->companyEmail(),
            'senha' => 'password', // Model will hash this automatically
            'cnpj' => $this->generateValidCnpj(),
            'endereco' => fake()->address(),
            'imagem' => fake()->optional(0.4)->imageUrl(200, 200, 'business'),
            'termos_aceitos' => true,
        ];
    }

    /**
     * Create an empresa with members.
     */
    public function withMembros(int $count = 3): static
    {
        return $this->has(Membro::factory()->count($count), 'membros');
    }    /**
     * Set a specific password for the empresa.
     */
    public function withPassword(string $password): static
    {
        return $this->state(fn () => [
            'senha' => $password, // Model will hash this automatically
        ]);
    }

    /**
     * Create a small company (startup).
     */
    public function startup(): static
    {
        return $this->state(fn () => [
            'nome' => fake()->company() . ' Startup',
        ])->afterCreating(function (Empresa $empresa) {
            // Create 1-5 members for startup
            Membro::factory()->count(fake()->numberBetween(1, 5))->create([
                'empresa_id' => $empresa->id,
            ]);
        });
    }

    /**
     * Create a medium-sized company.
     */
    public function medium(): static
    {
        return $this->state(fn () => [
            'nome' => fake()->company() . ' Ltd',
        ])->afterCreating(function (Empresa $empresa) {
            // Create 6-20 members for medium company
            Membro::factory()->count(fake()->numberBetween(6, 20))->create([
                'empresa_id' => $empresa->id,
            ]);
        });
    }

    /**
     * Create a large enterprise.
     */
    public function enterprise(): static
    {
        return $this->state(fn () => [
            'nome' => fake()->company() . ' Corporation',
        ])->afterCreating(function (Empresa $empresa) {
            // Create 21-50 members for enterprise
            Membro::factory()->count(fake()->numberBetween(21, 50))->create([
                'empresa_id' => $empresa->id,
            ]);
        });
    }

    /**
     * Create a company with logo/image.
     */
    public function withLogo(): static
    {
        return $this->state(fn () => [
            'imagem' => fake()->imageUrl(200, 200, 'business'),
        ]);
    }

    /**
     * Generate a valid CNPJ for testing.
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

    /**
     * Configure the factory to handle callbacks.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Empresa $empresa) {
            Log::debug('Empresa factory: Making empresa', ['email' => $empresa->email]);
        })->afterCreating(function (Empresa $empresa) {
            Log::debug('Empresa factory: Created empresa', ['id' => $empresa->id, 'email' => $empresa->email]);
        });
    }
}
