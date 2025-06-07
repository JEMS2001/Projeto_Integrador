<?php

declare(strict_types=1);

namespace App\Services\Auth;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Laravel\Passport\Token;
use Laravel\Passport\RefreshToken;

class TokenService
{
    /**
     * Store token information in cache for quick access
     */
    public function cacheTokenInfo(string $tokenId, array $tokenData): void
    {
        $cacheKey = "passport_token:{$tokenId}";
        $ttl = Carbon::parse($tokenData['expires_at'])->diffInMinutes(now());
        
        Cache::put($cacheKey, $tokenData, $ttl);
    }

    /**
     * Get cached token information
     */
    public function getCachedTokenInfo(string $tokenId): ?array
    {
        $cacheKey = "passport_token:{$tokenId}";
        return Cache::get($cacheKey);
    }

    /**
     * Invalidate cached token
     */
    public function invalidateTokenCache(string $tokenId): void
    {
        $cacheKey = "passport_token:{$tokenId}";
        Cache::forget($cacheKey);
    }

    /**
     * Check if token is still valid
     */
    public function isTokenValid(string $tokenId): bool
    {
        $cachedToken = $this->getCachedTokenInfo($tokenId);
        
        if ($cachedToken) {
            return Carbon::parse($cachedToken['expires_at'])->isFuture();
        }

        $token = Token::find($tokenId);
        return $token && !$token->revoked && $token->expires_at->isFuture();
    }

    /**
     * Refresh token if needed and return new token data
     */
    public function refreshTokenIfNeeded(Model $user, string $currentTokenId): ?array
    {
        $token = Token::find($currentTokenId);
        
        if (!$token || $token->revoked) {
            return null;
        }

        // Check if token expires within 24 hours
        if ($token->expires_at->diffInHours(now()) < 24) {
            $refreshToken = RefreshToken::where('access_token_id', $token->id)->first();
            
            if ($refreshToken && !$refreshToken->revoked) {
                // Create new token
                $newToken = $user->createToken(
                    $token->name,
                    $token->scopes
                );

                // Revoke old tokens
                $token->revoke();
                $refreshToken->revoke();

                return [
                    'access_token' => $newToken->accessToken,
                    'token_type' => 'Bearer',
                    'expires_at' => $newToken->token->expires_at,
                    'scopes' => $newToken->token->scopes,
                ];
            }
        }

        return null;
    }

    /**
     * Store session data for authenticated user
     */
    public function storeUserSession(Model $user, string $tokenId, array $additionalData = []): void
    {
        $userType = $user instanceof \App\Models\Empresa ? 'empresa' : 'membro';
        
        $sessionData = [
            'user_id' => $user->id,
            'user_type' => $userType,
            'token_id' => $tokenId,
            'login_time' => now()->toISOString(),
            'last_activity' => now()->toISOString(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            ...$additionalData
        ];

        Cache::put("user_session:{$user->id}:{$userType}", $sessionData, now()->addDays(30));
    }

    /**
     * Get user session data
     */
    public function getUserSession(int $userId, string $userType): ?array
    {
        return Cache::get("user_session:{$userId}:{$userType}");
    }

    /**
     * Update last activity for user session
     */
    public function updateLastActivity(int $userId, string $userType): void
    {
        $sessionData = $this->getUserSession($userId, $userType);
        
        if ($sessionData) {
            $sessionData['last_activity'] = now()->toISOString();
            Cache::put("user_session:{$userId}:{$userType}", $sessionData, now()->addDays(30));
        }
    }

    /**
     * Clear user session
     */
    public function clearUserSession(int $userId, string $userType): void
    {
        Cache::forget("user_session:{$userId}:{$userType}");
    }

    /**
     * Get all active sessions for a user
     */
    public function getActiveSessions(Model $user): array
    {
        $userType = $user instanceof \App\Models\Empresa ? 'empresa' : 'membro';
        $tokens = $user->tokens()->where('revoked', false)
            ->where('expires_at', '>', now())
            ->get();

        $sessions = [];
        foreach ($tokens as $token) {
            $sessionData = $this->getUserSession($user->id, $userType);
            if ($sessionData && $sessionData['token_id'] === $token->id) {
                $sessions[] = [
                    'token_id' => $token->id,
                    'name' => $token->name,
                    'scopes' => $token->scopes,
                    'created_at' => $token->created_at,
                    'expires_at' => $token->expires_at,
                    'last_activity' => $sessionData['last_activity'] ?? null,
                    'ip_address' => $sessionData['ip_address'] ?? null,
                    'user_agent' => $sessionData['user_agent'] ?? null,
                ];
            }
        }

        return $sessions;
    }

    /**
     * Revoke specific session
     */
    public function revokeSession(Model $user, string $tokenId): bool
    {
        $token = $user->tokens()->where('id', $tokenId)->first();
        
        if ($token) {
            $token->revoke();
            $this->invalidateTokenCache($tokenId);
            
            $userType = $user instanceof \App\Models\Empresa ? 'empresa' : 'membro';
            $sessionData = $this->getUserSession($user->id, $userType);
            
            if ($sessionData && $sessionData['token_id'] === $tokenId) {
                $this->clearUserSession($user->id, $userType);
            }
            
            return true;
        }

        return false;
    }

    /**
     * Clean expired tokens and sessions
     */
    public function cleanupExpiredTokens(): int
    {
        $expiredTokens = Token::where('expires_at', '<', now())
            ->where('revoked', false)
            ->get();

        $count = 0;
        foreach ($expiredTokens as $token) {
            $token->revoke();
            $this->invalidateTokenCache($token->id);
            $count++;
        }

        return $count;
    }

    /**
     * Count expired tokens that would be cleaned up
     */
    public function countExpiredTokens(int $daysOld = 30): int
    {
        $cutoffDate = now()->subDays($daysOld);
        
        return Token::where(function ($query) use ($cutoffDate) {
            $query->where('expires_at', '<', now())
                  ->orWhere('revoked', true)
                  ->orWhere('created_at', '<', $cutoffDate);
        })->count();
    }

    /**
     * Update token last used timestamp
     */
    public function updateTokenLastUsed(string $tokenId): void
    {
        Token::where('id', $tokenId)
            ->update(['last_used_at' => now()]);
            
        // Update cache if exists
        $cachedToken = $this->getCachedTokenInfo($tokenId);
        if ($cachedToken) {
            $cachedToken['last_used_at'] = now()->toISOString();
            $this->cacheTokenInfo($tokenId, $cachedToken);
        }
    }

    /**
     * Get token statistics for a user
     */
    public function getTokenStats(Model $user): array
    {
        $totalTokens = $user->tokens()->count();
        $activeTokens = $user->tokens()
            ->where('revoked', false)
            ->where('expires_at', '>', now())
            ->count();
        $expiredTokens = $user->tokens()
            ->where('expires_at', '<', now())
            ->count();
        $revokedTokens = $user->tokens()
            ->where('revoked', true)
            ->count();

        return [
            'total' => $totalTokens,
            'active' => $activeTokens,
            'expired' => $expiredTokens,
            'revoked' => $revokedTokens,
        ];
    }

    /**
     * Revoke a specific token by ID
     */
    public function revokeToken(string $tokenId, Model $user): bool
    {
        $token = $user->tokens()
            ->where('id', $tokenId)
            ->where('revoked', false)
            ->first();

        if (!$token) {
            return false;
        }

        // Revoke the access token
        $token->revoke();

        // Revoke associated refresh token
        RefreshToken::where('access_token_id', $token->id)
            ->update(['revoked' => true]);

        // Clear from cache
        $this->invalidateTokenCache($tokenId);

        return true;
    }
}
