<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

use Illuminate\Http\Request;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Exists;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Attributes\Validation\DateFormat;
use Spatie\LaravelData\Attributes\Validation\Accepted;
use Spatie\LaravelData\Data;

/**
 * DTO for Member registration
 * 
 * Enhanced with modern Laravel 11+ standards and comprehensive validation
 */
final class RegisterMembroDTO extends Data
{
    public function __construct(
        #[Required, StringType, Max(255)]
        public readonly string $nome,
        
        #[Required, Email, Max(255), Unique('membros', 'email')]
        public readonly string $email,
        
        #[Required, StringType]
        public readonly string $senha,
        
        #[Required, StringType, Unique('membros', 'cpf')]
        public readonly string $cpf,
        
        #[StringType, Max(20)]
        public readonly ?string $telefone,
        
        #[DateFormat('Y-m-d')]
        public readonly ?string $dataNascimento,
        
        #[Required, Exists('empresas', 'id')]
        public readonly int $empresaId,
        
        public readonly ?string $imagem,
        
        #[Required, Accepted]
        public readonly bool $termosAceitos,
    ) {
    }    /**
     * Create DTO from HTTP Request with enhanced data processing
     */
    public static function fromRequest(Request $request): self
    {
        return new self(
            nome: $request->string('nome')->trim()->toString(),
            email: $request->string('email')->lower()->trim()->toString(),
            senha: $request->string('senha')->toString(),
            cpf: static::formatCpf($request->string('cpf')->toString()),
            telefone: $request->filled('telefone') ? static::formatPhone($request->string('telefone')->toString()) : null,
            dataNascimento: $request->date('data_nascimento')?->format('Y-m-d'),
            empresaId: $request->integer('empresa_id'),
            imagem: $request->file('imagem')?->store('membros', 'public'),
            termosAceitos: $request->boolean('termos_aceitos', false),
        );
    }

    /**
     * Create DTO from array data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            nome: trim($data['nome']),
            email: strtolower(trim($data['email'])),
            senha: $data['senha'],
            cpf: static::formatCpf($data['cpf']),
            telefone: isset($data['telefone']) ? static::formatPhone($data['telefone']) : null,
            dataNascimento: $data['data_nascimento'] ?? null,
            empresaId: (int) $data['empresa_id'],
            imagem: $data['imagem'] ?? null,
            termosAceitos: (bool) ($data['termos_aceitos'] ?? false),
        );
    }

    /**
     * Format CPF to keep only numbers
     */
    private static function formatCpf(string $cpf): string
    {
        return preg_replace('/\D/', '', $cpf);
    }

    /**
     * Format phone number to keep only numbers
     */
    private static function formatPhone(string $phone): string
    {
        return preg_replace('/\D/', '', $phone);
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
