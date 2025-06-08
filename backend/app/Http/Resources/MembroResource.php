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
            'dataNascimento' => $this->getFormattedDataNascimento(),
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

    /**
     * Get formatted data nascimento handling both string and Carbon date
     */
    private function getFormattedDataNascimento(): ?string
    {
        if (!$this->data_nascimento) {
            return null;
        }

        // If it's already a Carbon instance, format it
        if ($this->data_nascimento instanceof \Carbon\Carbon) {
            return $this->data_nascimento->format('d/m/Y');
        }

        // If it's a string, try to parse and format it
        if (is_string($this->data_nascimento)) {
            try {
                return \Carbon\Carbon::parse($this->data_nascimento)->format('d/m/Y');
            } catch (\Exception $e) {
                // If parsing fails, return the original string
                return $this->data_nascimento;
            }
        }

        return null;
    }
}
