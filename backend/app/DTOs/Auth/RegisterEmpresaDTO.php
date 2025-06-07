<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

use Illuminate\Http\Request;
use Spatie\LaravelData\Data;

final class RegisterEmpresaDTO extends Data
{
    public function __construct(
        public readonly string $nome,
        public readonly string $cnpj,
        public readonly string $email,
        public readonly string $senha,
        public readonly ?string $endereco,
        public readonly ?string $imagem,
        public readonly bool $termosAceitos,
    ) {
    }    public static function fromRequest(Request $request): self
    {
        return new self(
            nome: $request->input('nome'),
            cnpj: $request->input('cnpj'),
            email: $request->input('email'),
            senha: $request->input('senha'),
            endereco: $request->input('endereco'),
            imagem: $request->file('imagem')?->store('empresas', 'public'),
            termosAceitos: filter_var($request->input('termos_aceitos', false), FILTER_VALIDATE_BOOLEAN),
        );
    }    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'nome' => $this->nome,
            'cnpj' => $this->cnpj,
            'email' => $this->email,
            'senha' => $this->senha, // Consider hashing this in the service/action layer
            'endereco' => $this->endereco,
            'imagem' => $this->imagem,
            'termos_aceitos' => $this->termosAceitos,
        ];
    }
}
