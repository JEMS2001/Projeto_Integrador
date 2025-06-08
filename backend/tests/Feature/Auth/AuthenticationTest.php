<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\Membro;
use App\Models\Empresa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for authentication routes and factory integration.
 * 
 * Tests the authentication flow with factory-generated data.
 */
final class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Set up the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create Passport personal access clients for testing
        $this->artisan('passport:client', [
            '--personal' => true,
            '--name' => 'Test Personal Access Client',
            '--provider' => 'users'
        ]);

        $this->artisan('passport:client', [
            '--personal' => true,
            '--name' => 'Membros Personal Access Client',
            '--provider' => 'membros'
        ]);

        $this->artisan('passport:client', [
            '--personal' => true,
            '--name' => 'Empresas Personal Access Client',
            '--provider' => 'empresas'
        ]);
    }

    /**
     * Test that factories can create models correctly.
     */
    public function test_factories_can_create_models(): void
    {
        // Test User factory
        $user = User::factory()->create();
        $this->assertModelExists($user);
        $this->assertNotNull($user->email);
        $this->assertNotNull($user->password);

        // Test Empresa factory
        $empresa = Empresa::factory()->create();
        $this->assertModelExists($empresa);
        $this->assertNotNull($empresa->cnpj);
        $this->assertNotNull($empresa->email);

        // Test Membro factory
        $membro = Membro::factory()->create();
        $this->assertModelExists($membro);
        $this->assertNotNull($membro->cpf);
        $this->assertNotNull($membro->email);
    }

    /**
     * Test user registration with factory data.
     */
    public function test_user_can_register(): void
    {
        $userData = User::factory()->make()->toArray();
        $userData['password'] = 'password123';
        $userData['password_confirmation'] = 'password123';

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => $userData['email'],
        ]);
    }    /**
     * Test member registration with factory data.
     */
    public function test_membro_can_register(): void
    {
        $empresa = Empresa::factory()->create();
        $membroData = Membro::factory()->make([
            'empresa_id' => $empresa->id,
        ])->toArray();
        $membroData['senha'] = 'password123';
        $membroData['senha_confirmation'] = 'password123';
        // Ensure termos_aceitos is set to true
        $membroData['termos_aceitos'] = true;
        // Ensure data_nascimento is in the correct format
        $membroData['data_nascimento'] = '1990-01-01';

        $response = $this->postJson('/api/auth/membros/register', $membroData);

        if ($response->status() !== 201) {
            dd($response->json(), $response->status());
        }

        $response->assertStatus(201);
        $this->assertDatabaseHas('membros', [
            'email' => $membroData['email'],
            'empresa_id' => $empresa->id,
        ]);
    }/**
     * Test empresa registration with factory data.
     */
    public function test_empresa_can_register(): void
    {
        $empresaData = Empresa::factory()->make()->toArray();
        $empresaData['senha'] = 'password123';
        $empresaData['senha_confirmation'] = 'password123';
        // Ensure required fields are set
        $empresaData['termos_aceitos'] = true;

        $response = $this->postJson('/api/auth/empresas/register', $empresaData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('empresas', [
            'email' => $empresaData['email'],
        ]);
    }    /**
     * Test user login using direct login routes (without tipo_usuario).
     */
    public function test_user_can_login(): void
    {
        // Skip for now as basic users use different authentication mechanism
        $this->markTestSkipped('Basic user login uses different mechanism - will be implemented later');
        
        // $user = User::factory()->withPassword('password123')->create();
        // Test will be implemented when user login endpoint is clarified
    }    /**
     * Test membro login with factory-created member.
     */
    public function test_membro_can_login(): void
    {
        $plainPassword = 'password123';
        $membro = Membro::factory()->withPassword($plainPassword)->create();        $response = $this->postJson('/api/auth/login', [
            'email' => $membro->email,
            'senha' => $plainPassword,
            'tipo_usuario' => 'membro',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'user',
                'user_type',
                'token',
                'token_type',
                'expires_at',
                'scope',
            ],
        ]);
        
        // Verify the user_type is correct
        $this->assertEquals('membro', $response->json('data.user_type'));
    }    /**
     * Test empresa login with factory-created company.
     */
    public function test_empresa_can_login(): void
    {
        $plainPassword = 'password123';
        $empresa = Empresa::factory()->withPassword($plainPassword)->create();        $response = $this->postJson('/api/auth/login', [
            'email' => $empresa->email,
            'senha' => $plainPassword,
            'tipo_usuario' => 'empresa',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'user',
                'user_type',
                'token',
                'token_type',
                'expires_at',
                'scope',
            ],
        ]);
        
        // Verify the user_type is correct
        $this->assertEquals('empresa', $response->json('data.user_type'));
    }

    /**
     * Test factory states work correctly.
     */
    public function test_factory_states_work_correctly(): void
    {
        // Test verified and unverified users
        $verifiedUser = User::factory()->verified()->create();
        $unverifiedUser = User::factory()->unverified()->create();

        $this->assertNotNull($verifiedUser->email_verified_at);
        $this->assertNull($unverifiedUser->email_verified_at);

        // Test empresa types
        $startup = Empresa::factory()->startup()->create();
        $enterprise = Empresa::factory()->enterprise()->create();

        $this->assertStringContainsString('Startup', $startup->nome);
        $this->assertStringContainsString('Corporation', $enterprise->nome);

        // Test membro states
        $activeMembro = Membro::factory()->active()->create();
        $inactiveMembro = Membro::factory()->inactive()->create();

        $this->assertNotNull($activeMembro->ultimo_login);
        // Inactive member might have null ultimo_login
    }

    /**
     * Test relationships work with factories.
     */
    public function test_factory_relationships_work(): void
    {
        // Create empresa with members
        $empresa = Empresa::factory()->withMembros(3)->create();
        
        $this->assertCount(3, $empresa->membros);
        $empresa->membros->each(function ($membro) use ($empresa) {
            $this->assertEquals($empresa->id, $membro->empresa_id);
        });

        // Create membro with specific empresa
        $specificEmpresa = Empresa::factory()->create();
        $membro = Membro::factory()->withEmpresa($specificEmpresa)->create();

        $this->assertEquals($specificEmpresa->id, $membro->empresa_id);
    }
}
