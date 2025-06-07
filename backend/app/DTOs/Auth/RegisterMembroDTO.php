<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

use Illuminate\Http\Request;
use Spatie\LaravelData\Data;

final class RegisterMembroDTO extends Data
{
    public function __construct(
        public readonly string $nome,
        public readonly string $email,
        public readonly string $senha,
        public readonly string $cpf,
        public readonly ?string $telefone,
        public readonly ?string $dataNascimento,
        public readonly int $empresaId,
        public readonly ?string $imagem,
        public readonly bool $termosAceitos,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            nome: $request->input('nome'),
            email: $request->input('email'),
            senha: $request->input('senha'),
            cpf: $request->input('cpf'),
            telefone: $request->input('telefone'),
            dataNascimento: $request->input('data_nascimento'),
            empresaId: (int) $request->input('empresa_id'),
            imagem: $request->file('imagem')?->store('membros', 'public'),
            termosAceitos: filter_var($request->input('termos_aceitos', false), FILTER_VALIDATE_BOOLEAN),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'nome' => $this->nome,
            'email' => $this->email,
            'senha' => $this->senha, // Consider hashing this in the service/action layer
            'cpf' => $this->cpf,
            'telefone' => $this->telefone,
            'data_nascimento' => $this->dataNascimento,
            'empresa_id' => $this->empresaId,
            'imagem' => $this->imagem,
            'termos_aceitos' => $this->termosAceitos,
        ];
    }
}
