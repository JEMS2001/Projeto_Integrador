<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

use Illuminate\Http\Request;
use Spatie\LaravelData\Data;

final class LoginDTO extends Data
{
    public function __construct(
        public readonly string $email,
        public readonly string $senha,
        public readonly string $tipoUsuario,
        public readonly bool $rememberMe = false,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            email: $request->input('email'),
            senha: $request->input('senha'),
            tipoUsuario: $request->input('tipo_usuario'),
            rememberMe: (bool) $request->input('remember_me', false),
        );
    }

    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'],
            senha: $data['senha'],
            tipoUsuario: $data['tipo_usuario'],
            rememberMe: (bool) ($data['remember_me'] ?? false),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'senha' => $this->senha,
            'tipo_usuario' => $this->tipoUsuario,
            'remember_me' => $this->rememberMe,
        ];
    }
}
