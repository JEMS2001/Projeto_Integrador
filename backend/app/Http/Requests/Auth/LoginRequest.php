<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email',
                'max:255',
            ],
            'senha' => [
                'required',
                'string',
                'min:6',
            ],
            'tipo_usuario' => [
                'required',
                'string',
                'in:membro,empresa',
            ],
            'remember_me' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */    public function messages(): array
    {
        return [
            'email.required' => 'O campo email é obrigatório.',
            'email.email' => 'O email deve ter um formato válido.',
            'email.max' => 'O email não pode ter mais que 255 caracteres.',
            'senha.required' => 'O campo senha é obrigatório.',
            'senha.min' => 'A senha deve ter pelo menos 6 caracteres.',
            'tipo_usuario.required' => 'O tipo de usuário é obrigatório.',
            'tipo_usuario.in' => 'O tipo de usuário deve ser "membro" ou "empresa".',
            'remember_me.boolean' => 'O campo "lembrar-me" deve ser verdadeiro ou falso.',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */    public function attributes(): array
    {
        return [
            'email' => 'email',
            'senha' => 'senha',
            'tipo_usuario' => 'tipo de usuário',
            'remember_me' => 'lembrar-me',
        ];
    }
}
