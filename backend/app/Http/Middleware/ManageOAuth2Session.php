<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Auth\TokenService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Token;
use Symfony\Component\HttpFoundation\Response;

class ManageOAuth2Session
{
    protected TokenService $tokenService;

    public function __construct(TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    /**
     * Handle an incoming request and manage OAuth2 session
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip session management for non-authenticated routes
        if (!$request->bearerToken()) {
            return $next($request);
        }

        $user = Auth::user();
        
        if ($user) {
            // Get current access token from request
            $token = $request->user()->token();
            
            if ($token instanceof Token) {
                // Update last activity - cast user to specific model
                $userModel = $user instanceof \App\Models\Empresa 
                    ? \App\Models\Empresa::find($user->id)
                    : \App\Models\Membro::find($user->id);
                
                if ($userModel) {
                    $userType = $user instanceof \App\Models\Empresa ? 'empresa' : 'membro';
                    $this->tokenService->updateLastActivity($userModel->id, $userType);

                    // Check if token needs refresh - pass the actual model
                    $refreshedToken = $this->tokenService->refreshTokenIfNeeded($userModel, $token->id);
                    
                    if ($refreshedToken) {
                        // Add new token to response headers
                        $response = $next($request);
                        $response->headers->set('X-New-Token', $refreshedToken['access_token']);
                        $response->headers->set('X-Token-Expires', $refreshedToken['expires_at']->toISOString());
                        
                        return $response;
                    }

                    // Cache token info for performance
                    $tokenData = [
                        'id' => $token->id,
                        'user_id' => $token->user_id,
                        'scopes' => $token->scopes,
                        'expires_at' => $token->expires_at->toISOString(),
                        'revoked' => $token->revoked,
                    ];
                    
                    $this->tokenService->cacheTokenInfo($token->id, $tokenData);
                }
            }
        }

        return $next($request);
    }
}
