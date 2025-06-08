<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Http\Middleware\PassportCookieManagement;
use App\Models\Empresa;
use App\Models\Membro;
use Illuminate\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Client;
use Laravel\Passport\RefreshToken;
use Laravel\Passport\Token;

/**
 * Service for managing OAuth2 refresh tokens and cookie-based authentication
 */
class RefreshTokenService
{
    /**
     * Refresh an access token using a refresh token
     */
    public function refreshAccessToken(string $refreshToken): ?array
    {
        try {
            // Find the password grant client
            $client = Client::where('password_client', true)->first();
            
            if (!$client) {
                Log::error('No password grant client found for token refresh');
                return null;
            }

            // Make request to OAuth token endpoint
            $response = Http::asForm()->post(url('/oauth/token'), [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
                'client_id' => $client->id,
                'client_secret' => $client->secret,
                'scope' => '', // Maintain existing scopes
            ]);

            if (!$response->successful()) {
                Log::warning('Failed to refresh token', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return null;
            }

            $tokenData = $response->json();
            
            Log::info('Access token refreshed successfully');
            
            return $tokenData;

        } catch (\Exception $e) {
            Log::error('Error refreshing access token: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Attempt to refresh token from cookie
     */
    public function refreshFromCookie(Request $request): ?array
    {
        $refreshToken = PassportCookieManagement::getRefreshTokenFromCookie($request);
        
        if (!$refreshToken) {
            return null;
        }

        return $this->refreshAccessToken($refreshToken);
    }

    /**
     * Validate if refresh token exists and is not expired
     */
    public function isRefreshTokenValid(string $refreshToken): bool
    {
        try {
            $token = RefreshToken::where('id', $refreshToken)
                ->where('revoked', false)
                ->first();

            if (!$token) {
                return false;
            }

            // Check if token is expired (if expires_at is set)
            if ($token->expires_at && $token->expires_at->isPast()) {
                return false;
            }

            return true;

        } catch (\Exception $e) {
            Log::error('Error validating refresh token: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get user from refresh token
     */
    public function getUserFromRefreshToken(string $refreshToken): ?Authenticatable
    {
        try {
            $refreshTokenModel = RefreshToken::where('id', $refreshToken)
                ->where('revoked', false)
                ->first();

            if (!$refreshTokenModel) {
                return null;
            }

            // Get the access token associated with this refresh token
            $accessToken = Token::find($refreshTokenModel->access_token_id);
            
            if (!$accessToken) {
                return null;
            }

            // Get the user based on provider
            $provider = $accessToken->provider ?? 'users';
            
            return $this->getUserByProvider($accessToken->user_id, $provider);

        } catch (\Exception $e) {
            Log::error('Error getting user from refresh token: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get user by provider type
     */
    private function getUserByProvider(int $userId, string $provider): ?Authenticatable
    {
        switch ($provider) {
            case 'users':
            case 'membros':
                return Membro::find($userId);
            
            case 'empresas':
                return Empresa::find($userId);
            
            default:
                Log::warning('Unknown provider type: ' . $provider);
                return null;
        }
    }

    /**
     * Revoke refresh token
     */
    public function revokeRefreshToken(string $refreshToken): bool
    {
        try {
            return RefreshToken::where('id', $refreshToken)
                ->update(['revoked' => true]) > 0;

        } catch (\Exception $e) {
            Log::error('Error revoking refresh token: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Revoke all refresh tokens for a user
     */
    public function revokeAllRefreshTokensForUser(Authenticatable $user): int
    {
        try {
            // Get user's access tokens
            $accessTokenIds = $user->tokens()
                ->where('revoked', false)
                ->pluck('id');

            if ($accessTokenIds->isEmpty()) {
                return 0;
            }

            // Revoke all refresh tokens for these access tokens
            return RefreshToken::whereIn('access_token_id', $accessTokenIds)
                ->where('revoked', false)
                ->update(['revoked' => true]);

        } catch (\Exception $e) {
            Log::error('Error revoking all refresh tokens for user: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Clean up expired refresh tokens
     */
    public function cleanupExpiredRefreshTokens(): int
    {
        try {
            return DB::table('oauth_refresh_tokens')
                ->where('expires_at', '<', now())
                ->orWhere('revoked', true)
                ->delete();

        } catch (\Exception $e) {
            Log::error('Error cleaning up expired refresh tokens: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get refresh token statistics
     */
    public function getRefreshTokenStats(): array
    {
        try {
            $total = RefreshToken::count();
            $active = RefreshToken::where('revoked', false)
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                })
                ->count();
            $expired = RefreshToken::where('expires_at', '<', now())->count();
            $revoked = RefreshToken::where('revoked', true)->count();

            return [
                'total' => $total,
                'active' => $active,
                'expired' => $expired,
                'revoked' => $revoked,
            ];

        } catch (\Exception $e) {
            Log::error('Error getting refresh token stats: ' . $e->getMessage());
            return [
                'total' => 0,
                'active' => 0,
                'expired' => 0,
                'revoked' => 0,
            ];
        }
    }
}
