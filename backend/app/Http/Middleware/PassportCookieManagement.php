<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cookie;
use Laravel\Passport\Passport;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\Cookie as HttpCookie;

/**
 * Middleware for managing OAuth2 cookies with Laravel Passport
 * Handles refresh token cookies and remember me functionality
 */
class PassportCookieManagement
{
    /**
     * Handle an incoming request and outgoing response for cookie management
     */
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        // Process the request
        $response = $next($request);

        // Handle refresh token cookies on authentication responses
        if ($this->isAuthenticationResponse($request, $response)) {
            $this->handleAuthenticationCookies($request, $response);
        }

        // Handle cookie cleanup on logout
        if ($this->isLogoutResponse($request, $response)) {
            $this->handleLogoutCookies($response);
        }

        return $response;
    }

    /**
     * Check if this is an authentication response that should set cookies
     */
    private function isAuthenticationResponse(Request $request, SymfonyResponse $response): bool
    {
        return $request->is('api/auth/login') && 
               $response->getStatusCode() === 200 &&
               $response instanceof Response;
    }

    /**
     * Check if this is a logout response that should clear cookies
     */
    private function isLogoutResponse(Request $request, SymfonyResponse $response): bool
    {
        return ($request->is('api/auth/logout') || $request->is('api/auth/logout-all')) && 
               $response->getStatusCode() === 200 &&
               $response instanceof Response;
    }

    /**
     * Handle setting authentication cookies for OAuth2 tokens
     */
    private function handleAuthenticationCookies(Request $request, Response $response): void
    {
        try {
            $responseData = json_decode($response->getContent(), true);
            
            if (!isset($responseData['data']['refresh_token'])) {
                return;
            }

            $refreshToken = $responseData['data']['refresh_token'];
            $rememberMe = $request->boolean('remember', false);
            
            // Set refresh token cookie duration based on remember me
            $cookieDuration = $rememberMe ? 
                config('passport.refresh_token_expires_in', 43200) : // 30 days in minutes
                config('passport.refresh_token_expires_in', 1440);   // 24 hours in minutes

            // Create secure refresh token cookie
            $refreshCookie = $this->createSecureRefreshTokenCookie(
                $refreshToken, 
                $cookieDuration
            );

            // Create remember me preference cookie
            $rememberCookie = $this->createRememberMeCookie($rememberMe);

            // Queue cookies for response
            Cookie::queue($refreshCookie);
            Cookie::queue($rememberCookie);

            // Remove refresh token from JSON response for security
            unset($responseData['data']['refresh_token']);
            $response->setContent(json_encode($responseData));

            Log::info('OAuth2 cookies set successfully', [
                'remember_me' => $rememberMe,
                'cookie_duration' => $cookieDuration
            ]);

        } catch (\Exception $e) {
            Log::error('Error setting authentication cookies: ' . $e->getMessage());
        }
    }

    /**
     * Handle clearing authentication cookies on logout
     */
    private function handleLogoutCookies(Response $response): void
    {
        try {
            // Clear refresh token cookie
            $clearRefreshCookie = cookie(
                'passport_refresh_token',
                '',
                -1, // Expire immediately
                '/',
                config('session.domain'),
                config('session.secure'),
                true // HTTP only
            );

            // Clear remember me cookie
            $clearRememberCookie = cookie(
                'passport_remember_me',
                '',
                -1, // Expire immediately
                '/',
                config('session.domain'),
                config('session.secure'),
                false // Not HTTP only (for JS access if needed)
            );

            // Queue cookies for clearing
            Cookie::queue($clearRefreshCookie);
            Cookie::queue($clearRememberCookie);

            Log::info('OAuth2 cookies cleared successfully');

        } catch (\Exception $e) {
            Log::error('Error clearing authentication cookies: ' . $e->getMessage());
        }
    }    /**
     * Create a secure refresh token cookie
     */
    private function createSecureRefreshTokenCookie(string $refreshToken, int $minutes)
    {
        return cookie(
            'passport_refresh_token',
            $refreshToken,
            $minutes,
            '/', // Path
            config('session.domain'), // Domain
            config('session.secure'), // Secure (HTTPS only)
            true, // HTTP only (prevents XSS)
            false, // Raw (don't encode)
            config('session.same_site') // SameSite
        );
    }    /**
     * Create remember me preference cookie
     */
    private function createRememberMeCookie(bool $rememberMe)
    {
        return cookie(
            'passport_remember_me',
            $rememberMe ? '1' : '0',
            43200, // 30 days
            '/',
            config('session.domain'),
            config('session.secure'),
            false, // Not HTTP only (for JS access)
            false,
            config('session.same_site')
        );
    }

    /**
     * Get refresh token from cookie
     */
    public static function getRefreshTokenFromCookie(Request $request): ?string
    {
        return $request->cookie('passport_refresh_token');
    }

    /**
     * Get remember me preference from cookie
     */
    public static function getRememberMeFromCookie(Request $request): bool
    {
        return $request->cookie('passport_remember_me') === '1';
    }

    /**
     * Check if user has valid authentication cookies
     */
    public static function hasValidAuthCookies(Request $request): bool
    {
        $refreshToken = self::getRefreshTokenFromCookie($request);
        return !empty($refreshToken);
    }
}
