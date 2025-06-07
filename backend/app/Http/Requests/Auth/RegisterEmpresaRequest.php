<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use App\Rules\CnpjRule; // Assuming you have a CnpjRule

final class RegisterEmpresaRequest extends FormRequest
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
            'nome_fantasia' => ['required', 'string', 'max:255'],
            'razao_social' => ['required', 'string', 'max:255'],
            'cnpj' => ['required', 'string', new CnpjRule(), 'unique:empresas,cnpj'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:empresas,email'],
            'senha' => ['required', 'string', Password::defaults(), 'confirmed'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'setor' => ['nullable', 'string', 'max:255'],
            'endereco' => ['nullable', 'string', 'max:255'],
            'cidade' => ['nullable', 'string', 'max:255'],
            'estado' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'url', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'], // Max 2MB
            'termos_aceitos' => ['required', 'accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nome_fantasia.required' => 'O campo nome fantasia é obrigatório.',
            'razao_social.required' => 'O campo razão social é obrigatório.',
            'cnpj.required' => 'O campo CNPJ é obrigatório.',
            'cnpj.unique' => 'Este CNPJ já está cadastrado.',
            'email.required' => 'O campo email é obrigatório.',
            'email.email' => 'Por favor, insira um email válido.',
            'email.unique' => 'Este email já está cadastrado.',
            'senha.required' => 'O campo senha é obrigatório.',
            'senha.confirmed' => 'A confirmação da senha não corresponde.',
            'logo.image' => 'O logo deve ser uma imagem.',
            'logo.mimes' => 'O logo deve ser um arquivo do tipo: jpeg, png, jpg, gif, svg.',
            'logo.max' => 'O logo não pode ser maior que 2MB.',
            'termos_aceitos.accepted' => 'Você deve aceitar os termos e condições.',
        ];
    }
}
