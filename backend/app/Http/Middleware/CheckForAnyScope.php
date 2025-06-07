<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Custom scope checking middleware that allows any of the specified scopes
 */
class CheckForAnyScope
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$scopes): mixed
    {
        if (!$request->user()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token de acesso não encontrado.'
            ], 401);
        }

        // Check if user has any of the required scopes
        $user = $request->user();
        
        // If using a custom token system, check token scopes
        if (method_exists($user, 'token') && $user->token()) {
            foreach ($scopes as $scope) {
                if (method_exists($user, 'tokenCan') && $user->tokenCan($scope)) {
                    return $next($request);
                }
            }
        }
        
        // Alternative: Check user permissions/roles directly
        if (method_exists($user, 'hasAnyScope')) {
            if ($user->hasAnyScope($scopes)) {
                return $next($request);
            }
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Acesso negado. Pelo menos um dos escopos é necessário.',
            'required_scopes' => $scopes
        ], 403);
    }
}
