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
 * Authenticated user information resource
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $this->resource;
        $userType = $this->getUserType($user);
        
        return [
            'user' => $this->getUserResource($user, $userType),
            'user_type' => $userType,
            'permissions' => $this->getUserPermissions($user),
            'last_login' => $user->updated_at?->toISOString(),
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

    /**
     * Get user permissions based on type
     */
    private function getUserPermissions($user): array
    {
        return match (true) {
            $user instanceof Empresa => [
                'can_manage_company',
                'can_manage_employees',
                'can_create_tasks',
                'can_view_analytics',
            ],
            $user instanceof Membro => [
                'can_view_tasks',
                'can_update_own_profile',
                'can_create_events',
            ],
            default => [],
        };
    }
}
