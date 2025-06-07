<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmpresaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'email' => $this->email,
            'cnpj' => $this->getCnpjFormatted(),
            'endereco' => $this->endereco,
            'membros' => $this->whenLoaded('membros', function () {
                return $this->membros->map(function ($membro) {
                    return [
                        'id' => $membro->id,
                        'nome' => $membro->nome,
                        'email' => $membro->email,
                        'cpf' => $membro->getCpfFormatted(),
                    ];
                });
            }),
            'totalMembros' => $this->whenLoaded('membros', function () {
                return $this->membros->count();
            }),
            'imagem' => $this->imagem,
            'criadoEm' => $this->created_at?->format('d/m/Y H:i:s'),
            'atualizadoEm' => $this->updated_at?->format('d/m/Y H:i:s'),
        ];
    }

    /**
     * Get formatted CNPJ
     */
    private function getCnpjFormatted(): string
    {
        if (!$this->cnpj) {
            return '';
        }
        
        $cnpjNumbers = preg_replace('/\D/', '', $this->cnpj);
        
        if (strlen($cnpjNumbers) === 14) {
            return substr($cnpjNumbers, 0, 2) . '.' . 
                   substr($cnpjNumbers, 2, 3) . '.' . 
                   substr($cnpjNumbers, 5, 3) . '/' . 
                   substr($cnpjNumbers, 8, 4) . '-' . 
                   substr($cnpjNumbers, 12, 2);
        }
        
        return $this->cnpj;
    }
}
