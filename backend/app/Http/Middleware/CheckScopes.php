<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Custom scope checking middleware for API routes
 */
class CheckScopes
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$scopes): mixed
    {
        if (!$request->user() || !$request->user()->token()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token de acesso não encontrado.'
            ], 401);
        }

        foreach ($scopes as $scope) {
            if (!$request->user()->tokenCan($scope)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Acesso negado. Escopo insuficiente.',
                    'required_scope' => $scope
                ], 403);
            }
        }

        return $next($request);
    }
}
