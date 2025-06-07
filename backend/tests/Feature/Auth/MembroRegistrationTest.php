<?php

namespace Tests\Feature\Auth;

use App\Models\Empresa;
use App\Models\Membro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MembroRegistrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function can_register_membro_successfully()
    {
        $empresa = Empresa::factory()->create();        $data = [
            'nome' => 'João Silva',
            'email' => 'joao@example.com',
            'senha' => 'MinhaSenh@123',
            'cpf' => '12345678901',
            'dataNascimento' => '1990-05-15',
            'telefone' => '11987654321',
            'empresaId' => $empresa->id,
            'imagem' => 'https://example.com/avatar.jpg'
        ];

        $response = $this->postJson('/api/auth/register/membro', $data);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'user' => [
                            'id',
                            'nome',
                            'email',
                            'cpf',
                            'dataNascimento',
                            'telefone',
                            'empresa',
                            'imagem',
                            'criadoEm',
                            'atualizadoEm'
                        ],
                        'token'
                    ]
                ]);

        $this->assertDatabaseHas('membros', [
            'nome' => 'João Silva',
            'email' => 'joao@example.com',
            'cpf' => '123.456.789-01'
        ]);
    }

    /** @test */
    public function cannot_register_membro_with_invalid_cpf()
    {
        $data = [
            'nome' => 'João Silva',
            'email' => 'joao@example.com',
            'senha' => 'MinhaSenh@123',
            'cpf' => '12345678900', // Invalid CPF
            'dataNascimento' => '1990-05-15'
        ];

        $response = $this->postJson('/api/auth/register/membro', $data);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['cpf']);
    }

    /** @test */
    public function cannot_register_membro_with_duplicate_email()
    {
        Membro::factory()->create(['email' => 'joao@example.com']);

        $data = [
            'nome' => 'João Silva',
            'email' => 'joao@example.com',
            'senha' => 'MinhaSenh@123',
            'cpf' => '12345678901',
            'dataNascimento' => '1990-05-15'
        ];

        $response = $this->postJson('/api/auth/register/membro', $data);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function cannot_register_membro_with_duplicate_cpf()
    {
        Membro::factory()->create(['cpf' => '123.456.789-01']);

        $data = [
            'nome' => 'João Silva',
            'email' => 'joao@example.com',
            'senha' => 'MinhaSenh@123',
            'cpf' => '12345678901',
            'dataNascimento' => '1990-05-15'
        ];

        $response = $this->postJson('/api/auth/register/membro', $data);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['cpf']);
    }

    /** @test */
    public function cannot_register_membro_with_weak_password()
    {
        $data = [
            'nome' => 'João Silva',
            'email' => 'joao@example.com',
            'senha' => '123456', // Weak password
            'cpf' => '12345678901',
            'dataNascimento' => '1990-05-15'
        ];

        $response = $this->postJson('/api/auth/register/membro', $data);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['senha']);
    }

    /** @test */
    public function validates_required_fields()
    {
        $response = $this->postJson('/api/auth/register/membro', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'nome', 'email', 'senha', 'cpf', 'dataNascimento'
                ]);
    }
}
