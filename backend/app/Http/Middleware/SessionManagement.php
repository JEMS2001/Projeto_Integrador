<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\Auth\TokenService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Token;
use Symfony\Component\HttpFoundation\Response;

final class SessionManagement
{
    public function __construct(
        private readonly TokenService $tokenService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only process if user is authenticated via Passport
        if (!Auth::guard('api')->check()) {
            return $next($request);
        }

        $user = Auth::guard('api')->user();
        $token = $request->user()->token();

        if ($token instanceof Token) {
            // Update last used timestamp
            $this->tokenService->updateTokenLastUsed($token->id);

            // Add session info to request
            $request->merge([
                'session_info' => [
                    'token_id' => $token->id,
                    'token_name' => $token->name,
                    'scopes' => $token->scopes,
                    'expires_at' => $token->expires_at,
                    'created_at' => $token->created_at,
                    'last_used_at' => now(),
                ]
            ]);

            // Add user context for session management
            $request->attributes->set('current_token_id', $token->id);
            $request->attributes->set('user_id', $user->id);
        }

        return $next($request);
    }
}
