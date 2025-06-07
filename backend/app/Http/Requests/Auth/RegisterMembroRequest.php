<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use App\Rules\CpfRule; // Assuming you have a CpfRule

final class RegisterMembroRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Anyone can attempt to register
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:membros,email'],
            'senha' => ['required', 'string', Password::defaults(), 'confirmed'],
            'cpf' => ['required', 'string', new CpfRule(), 'unique:membros,cpf'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'data_nascimento' => ['nullable', 'date_format:Y-m-d'],
            'linkedin' => ['nullable', 'string', 'url', 'max:255'],
            'github' => ['nullable', 'string', 'url', 'max:255'],
            'nivel_experiencia' => ['nullable', 'string', 'max:255'],
            'especialidade' => ['nullable', 'string', 'max:255'],
            'instituicao_ensino' => ['nullable', 'string', 'max:255'],
            'curso' => ['nullable', 'string', 'max:255'],
            'tipo_formacao' => ['nullable', 'string', 'max:255'],
            'curriculo' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:2048'], // Max 2MB
            'termos_aceitos' => ['required', 'accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nome.required' => 'O campo nome é obrigatório.',
            'email.required' => 'O campo email é obrigatório.',
            'email.email' => 'Por favor, insira um email válido.',
            'email.unique' => 'Este email já está cadastrado.',
            'senha.required' => 'O campo senha é obrigatório.',
            'senha.confirmed' => 'A confirmação da senha não corresponde.',
            'cpf.required' => 'O campo CPF é obrigatório.',
            'cpf.unique' => 'Este CPF já está cadastrado.',
            'curriculo.mimes' => 'O currículo deve ser um arquivo do tipo: pdf, doc, docx.',
            'curriculo.max' => 'O currículo não pode ser maior que 2MB.',
            'termos_aceitos.accepted' => 'Você deve aceitar os termos e condições.',
        ];
    }
}
