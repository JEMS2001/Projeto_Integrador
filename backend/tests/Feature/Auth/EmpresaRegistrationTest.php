<?php

namespace Tests\Feature\Auth;

use App\Models\Empresa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EmpresaRegistrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function can_register_empresa_successfully()
    {
        $data = [
            'nome' => 'Empresa Tech LTDA',
            'email' => 'contato@empresatech.com',
            'senha' => 'MinhaSenh@123',
            'cnpj' => '12345678000195',
            'endereco' => 'Rua das Flores, 123, São Paulo - SP',
            'imagem' => 'https://example.com/logo.jpg'
        ];

        $response = $this->postJson('/api/auth/register/empresa', $data);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'empresa' => [
                            'id',
                            'nome',
                            'email',
                            'cnpj',
                            'endereco',
                            'imagem',
                            'criadoEm',
                            'atualizadoEm'
                        ]
                    ]
                ]);

        $this->assertDatabaseHas('empresas', [
            'nome' => 'Empresa Tech LTDA',
            'email' => 'contato@empresatech.com',
            'cnpj' => '12.345.678/0001-95'
        ]);
    }

    /** @test */
    public function cannot_register_empresa_with_invalid_cnpj()
    {
        $data = [
            'nome' => 'Empresa Tech LTDA',
            'email' => 'contato@empresatech.com',
            'senha' => 'MinhaSenh@123',
            'cnpj' => '12345678000100' // Invalid CNPJ
        ];

        $response = $this->postJson('/api/auth/register/empresa', $data);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['cnpj']);
    }

    /** @test */
    public function cannot_register_empresa_with_duplicate_email()
    {
        Empresa::factory()->create(['email' => 'contato@empresatech.com']);

        $data = [
            'nome' => 'Empresa Tech LTDA',
            'email' => 'contato@empresatech.com',
            'senha' => 'MinhaSenh@123',
            'cnpj' => '12345678000195'
        ];

        $response = $this->postJson('/api/auth/register/empresa', $data);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function cannot_register_empresa_with_duplicate_cnpj()
    {
        Empresa::factory()->create(['cnpj' => '12.345.678/0001-95']);

        $data = [
            'nome' => 'Empresa Tech LTDA',
            'email' => 'contato@empresatech.com',
            'senha' => 'MinhaSenh@123',
            'cnpj' => '12345678000195'
        ];

        $response = $this->postJson('/api/auth/register/empresa', $data);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['cnpj']);
    }

    /** @test */
    public function cannot_register_empresa_with_weak_password()
    {
        $data = [
            'nome' => 'Empresa Tech LTDA',
            'email' => 'contato@empresatech.com',
            'senha' => '123456', // Weak password
            'cnpj' => '12345678000195'
        ];

        $response = $this->postJson('/api/auth/register/empresa', $data);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['senha']);
    }

    /** @test */
    public function validates_required_fields()
    {
        $response = $this->postJson('/api/auth/register/empresa', []);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'nome', 'email', 'senha', 'cnpj'
                ]);
    }

    /** @test */
    public function validates_cnpj_format()
    {
        $data = [
            'nome' => 'Empresa Tech LTDA',
            'email' => 'contato@empresatech.com',
            'senha' => 'MinhaSenh@123',
            'cnpj' => '12345678' // Wrong format
        ];

        $response = $this->postJson('/api/auth/register/empresa', $data);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['cnpj']);
    }
}
