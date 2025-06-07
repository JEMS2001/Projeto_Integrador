<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MembroResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'email' => $this->email,
            'cpf' => $this->getCpfFormatted(),
            'dataNascimento' => $this->data_nascimento?->format('d/m/Y'),
            'telefone' => $this->telefone,
            'empresa' => $this->whenLoaded('empresa', function () {
                return [
                    'id' => $this->empresa->id,
                    'nome' => $this->empresa->nome,
                    'cnpj' => $this->empresa->getCnpjFormatted(),
                ];
            }),
            'imagem' => $this->imagem,
            'criadoEm' => $this->created_at?->format('d/m/Y H:i:s'),
            'atualizadoEm' => $this->updated_at?->format('d/m/Y H:i:s'),
        ];
    }

    /**
     * Get formatted CPF
     */
    private function getCpfFormatted(): string
    {
        if (!$this->cpf) {
            return '';
        }
        
        $cpfNumbers = preg_replace('/\D/', '', $this->cpf);
        
        if (strlen($cpfNumbers) === 11) {
            return substr($cpfNumbers, 0, 3) . '.' . 
                   substr($cpfNumbers, 3, 3) . '.' . 
                   substr($cpfNumbers, 6, 3) . '-' . 
                   substr($cpfNumbers, 9, 2);
        }
        
        return $this->cpf;
    }
}
