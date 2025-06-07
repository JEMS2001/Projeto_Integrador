<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Passport\Http\Middleware\CheckForAnyScope as PassportCheckForAnyScope;

/**
 * Custom scope checking middleware that allows any of the specified scopes
 */
class CheckForAnyScope extends PassportCheckForAnyScope
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
            if ($request->user()->tokenCan($scope)) {
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
