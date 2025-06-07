<?php

declare(strict_types=1);

namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SessionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource['id'],
            'name' => $this->resource['name'] ?? 'Default Token',
            'client' => $this->resource['client'] ?? 'API Client',
            'scopes' => $this->resource['scopes'] ?? [],
            'is_current' => $this->resource['is_current'] ?? false,
            'created_at' => $this->resource['created_at'],
            'expires_at' => $this->resource['expires_at'],
            'last_used_at' => $this->resource['last_used_at'] ?? null,
            'status' => $this->getStatus(),
            'time_remaining' => $this->getTimeRemaining(),
        ];
    }

    /**
     * Get session status
     */
    private function getStatus(): string
    {
        if ($this->resource['is_current'] ?? false) {
            return 'current';
        }

        $expiresAt = $this->resource['expires_at'];
        if ($expiresAt && now()->greaterThan($expiresAt)) {
            return 'expired';
        }

        return 'active';
    }

    /**
     * Get time remaining until expiration
     */
    private function getTimeRemaining(): ?string
    {
        $expiresAt = $this->resource['expires_at'];
        if (!$expiresAt) {
            return null;
        }

        $now = now();
        $expires = is_string($expiresAt) ? \Carbon\Carbon::parse($expiresAt) : $expiresAt;

        if ($expires->lessThan($now)) {
            return 'expired';
        }

        return $expires->diffForHumans($now, true);
    }
}
