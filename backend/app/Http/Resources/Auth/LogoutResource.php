<?php

declare(strict_types=1);

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Logout response resource
 */
class LogoutResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'message' => $this->resource['message'] ?? 'Logout realizado com sucesso!',
            'revoked_tokens' => $this->resource['revoked_tokens'] ?? 1,
            'logged_out_at' => now()->toISOString(),
        ];
    }
}
