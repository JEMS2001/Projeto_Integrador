<?php

declare(strict_types=1);

namespace App\Http\Resources\Auth;

use App\Http\Resources\EmpresaResource;
use App\Http\Resources\MembroResource;
use App\Models\Empresa;
use App\Models\Membro;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Authentication response resource
 */
class AuthResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $this->resource['user'];
        $userType = $this->getUserType($user);
        
        return [
            'user' => $this->getUserResource($user, $userType),
            'user_type' => $userType,
            'token' => $this->resource['token'],
            'token_type' => $this->resource['token_type'] ?? 'Bearer',
            'expires_at' => $this->resource['expires_at']?->toISOString(),
            'scope' => $this->resource['scope'] ?? [$userType],
        ];
    }

    /**
     * Get user type based on model instance
     */
    private function getUserType($user): string
    {
        return match (true) {
            $user instanceof Empresa => 'empresa',
            $user instanceof Membro => 'membro',
            default => 'unknown',
        };
    }

    /**
     * Get appropriate user resource
     */
    private function getUserResource($user, string $userType): ?JsonResource
    {
        return match ($userType) {
            'empresa' => new EmpresaResource($user),
            'membro' => new MembroResource($user),
            default => null,
        };
    }
}
