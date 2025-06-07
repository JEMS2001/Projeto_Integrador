<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\DTOs\Auth\LoginDTO;
use App\DTOs\Auth\RegisterEmpresaDTO;
use App\DTOs\Auth\RegisterMembroDTO;
use App\Models\Empresa;
use App\Models\Membro;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\PersonalAccessTokenResult;

class AuthService
{
    /**
     * Register a new company
     */
    public function registerEmpresa(RegisterEmpresaDTO $dto): Empresa
    {
        $empresa = Empresa::create([
            'nome' => $dto->nome,
            'cnpj' => $dto->cnpj,
            'endereco' => $dto->endereco,
            'email' => $dto->email,
            'senha' => $dto->senha, // Will be hashed by model boot method
            'imagem' => $dto->imagem,
        ]);

        return $empresa;
    }

    /**
     * Register a new member
     */
    public function registerMembro(RegisterMembroDTO $dto): Membro
    {
        $membro = Membro::create([
            'nome' => $dto->nome,
            'data_nascimento' => $dto->dataNascimento,
            'telefone' => $dto->telefone,
            'cpf' => $dto->cpf,
            'email' => $dto->email,
            'senha' => $dto->senha, // Will be hashed by model boot method
            'empresa_id' => $dto->empresaId,
            'imagem' => $dto->imagem,
        ]);

        return $membro;
    }    /**
     * Authenticate user and return token with Laravel Passport
     */
    public function login(LoginDTO $dto): array
    {
        $user = $this->findUserByEmailAndType($dto->email, $dto->tipoUsuario);

        if (!$user || !Hash::check($dto->senha, $user->senha)) {
            throw new AuthenticationException('Invalid credentials');
        }

        // Create OAuth2 token with Passport
        $tokenResult = $this->createTokenForUser($user, $dto->tipoUsuario, $dto->rememberMe);

        // Update last login timestamp
        $user->touch();

        return [
            'user' => $user,
            'token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => $tokenResult->token->expires_at,
            'scope' => [$dto->tipoUsuario],
            'remember_me' => $dto->rememberMe,
        ];
    }

    /**
     * Find user by email and type
     */
    private function findUserByEmailAndType(string $email, string $userType): ?Model
    {
        return match ($userType) {
            'empresa' => Empresa::where('email', $email)->first(),
            'membro' => Membro::where('email', $email)->first(),
            default => null,
        };
    }    /**
     * Create OAuth2 token for authenticated user with proper scopes
     */
    private function createTokenForUser(Model $user, string $userType, bool $rememberMe = false): PersonalAccessTokenResult
    {
        $scopes = [$userType];
        $tokenName = ucfirst($userType) . ' Access Token';

        // Add additional scopes based on user type
        if ($userType === 'empresa') {
            $scopes[] = 'company-management';
            $scopes[] = 'employee-management';
        } elseif ($userType === 'membro') {
            $scopes[] = 'task-management';
            $scopes[] = 'profile-management';
        }

        // Create token with extended expiration if remember me is enabled
        $token = $user->createToken($tokenName, $scopes);
        
        if ($rememberMe) {
            // Extend token expiration for remember me functionality
            $token->token->expires_at = now()->addDays(30);
            $token->token->save();
        }

        return $token;
    }/**
     * Logout user and revoke current token
     */
    public function logout($user): array
    {
        if (!$user) {
            throw new AuthenticationException('User not authenticated');
        }

        // Revoke current token for Passport
        $user->token()->revoke();

        return [
            'message' => 'Logout realizado com sucesso!',
            'revoked_tokens' => 1,
        ];
    }

    /**
     * Logout user from all devices (revoke all tokens)
     */
    public function logoutAll($user): array
    {
        if (!$user) {
            throw new AuthenticationException('User not authenticated');
        }

        // Count tokens before revoking
        $tokenCount = $user->tokens()->count();

        // Revoke all tokens for Passport
        $user->tokens()->delete();

        return [
            'message' => 'Logout de todos os dispositivos realizado com sucesso!',
            'revoked_tokens' => $tokenCount,
        ];
    }
}
