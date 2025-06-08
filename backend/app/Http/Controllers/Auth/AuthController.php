<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\DTOs\Auth\LoginDTO;
use App\DTOs\Auth\RegisterEmpresaDTO;
use App\DTOs\Auth\RegisterMembroDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterEmpresaRequest;
use App\Http\Requests\Auth\RegisterMembroRequest;
use App\Http\Resources\Auth\AuthResource;
use App\Http\Resources\Auth\LogoutResource;
use App\Http\Resources\Auth\SessionResource;
use App\Http\Resources\Auth\UserResource;
use App\Http\Resources\EmpresaResource;
use App\Http\Resources\MembroResource;
use App\Models\Empresa;
use App\Models\Membro;
use App\Services\Auth\AuthService;
use App\Services\Auth\TokenService;
use App\Services\Auth\RefreshTokenService;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Http\Middleware\PassportCookieManagement;

class AuthController extends Controller
{    public function __construct(
        private readonly AuthService $authService,
        private readonly TokenService $tokenService,
        private readonly RefreshTokenService $refreshTokenService
    ) {}

    /**
     * Register a new member
     */
    public function registerMembro(RegisterMembroRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $dto = RegisterMembroDTO::fromRequest($request);
            $membro = $this->authService->registerMembro($dto);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Membro registrado com sucesso!',
                'data' => [
                    'user' => new MembroResource($membro)
                ]
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro no registro de membro: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erro interno do servidor. Tente novamente.',
                'errors' => config('app.debug') ? [$e->getMessage()] : []
            ], 500);
        }
    }

    /**
     * Register a new company
     */
    public function registerEmpresa(RegisterEmpresaRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $dto = RegisterEmpresaDTO::fromRequest($request);
            $empresa = $this->authService->registerEmpresa($dto);

            DB::commit();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Empresa registrada com sucesso!',
                'data' => [
                    'empresa' => new EmpresaResource($empresa)
                ]
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro no registro de empresa: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erro interno do servidor. Tente novamente.',
                'errors' => config('app.debug') ? [$e->getMessage()] : []
            ], 500);
        }
    }    /**
     * Register a new basic user
     */
    public function register(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Validate request
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            // Create user
            $user = \App\Models\User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'email_verified_at' => $request->boolean('email_verified', false) ? now() : null,
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Usuário registrado com sucesso!',
                'data' => new UserResource($user)
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Dados de validação inválidos.',
                'errors' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro no registro de usuário: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erro interno do servidor. Tente novamente.',
                'errors' => config('app.debug') ? [$e->getMessage()] : []
            ], 500);
        }
    }

    /**
     * Login user (Member or Company)
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $dto = LoginDTO::fromRequest($request);
            $authData = $this->authService->login($dto);

            return response()->json([
                'status' => 'success',
                'message' => 'Login realizado com sucesso!',
                'data' => new AuthResource($authData)
            ]);

        } catch (AuthenticationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Credenciais inválidas. Verifique seu email e senha.',
                'errors' => [
                    'email' => ['Email ou senha incorretos.']
                ]
            ], 422);

        } catch (Exception $e) {
            Log::error('Erro no login: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erro interno do servidor. Tente novamente.',
                'errors' => config('app.debug') ? [$e->getMessage()] : []
            ], 500);
        }
    }    /**
     * Login specifically for Membro
     */
    public function loginMembro(Request $request): JsonResponse
    {
        try {
            // Validate request
            $request->validate([
                'email' => ['required', 'email'],
                'senha' => ['required', 'string'],
            ]);

            // Find membro
            $membro = Membro::where('email', $request->email)->first();

            if (!$membro || !Hash::check($request->senha, $membro->senha)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Credenciais inválidas.',
                    'errors' => [
                        'email' => ['Email ou senha incorretos.']
                    ]
                ], 422);
            }

            // Create token
            $token = $membro->createToken('auth-token')->accessToken;

            // Update last login
            $membro->update(['ultimo_login' => now()]);

            return response()->json([
                'status' => 'success',
                'message' => 'Login realizado com sucesso!',
                'data' => [
                    'membro' => new MembroResource($membro),
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'expires_in' => config('passport.personal_access_client.expires_in', 3600),
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dados de validação inválidos.',
                'errors' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            Log::error('Erro no login de membro: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erro interno do servidor. Tente novamente.',
                'errors' => config('app.debug') ? [$e->getMessage()] : []
            ], 500);
        }
    }

    /**
     * Login specifically for Empresa
     */
    public function loginEmpresa(Request $request): JsonResponse
    {
        try {
            // Validate request
            $request->validate([
                'email' => ['required', 'email'],
                'senha' => ['required', 'string'],
            ]);

            // Find empresa
            $empresa = Empresa::where('email', $request->email)->first();

            if (!$empresa || !Hash::check($request->senha, $empresa->senha)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Credenciais inválidas.',
                    'errors' => [
                        'email' => ['Email ou senha incorretos.']
                    ]
                ], 422);
            }

            // Create token
            $token = $empresa->createToken('auth-token')->accessToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Login realizado com sucesso!',
                'data' => [
                    'empresa' => new EmpresaResource($empresa),
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'expires_in' => config('passport.personal_access_client.expires_in', 3600),
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Dados de validação inválidos.',
                'errors' => $e->errors()
            ], 422);

        } catch (Exception $e) {
            Log::error('Erro no login de empresa: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erro interno do servidor. Tente novamente.',
                'errors' => config('app.debug') ? [$e->getMessage()] : []
            ], 500);
        }
    }    /**
     * Get authenticated user information
     */
    public function user(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Usuário não autenticado.'
                ], 401);
            }

            return response()->json([
                'status' => 'success',
                'data' => new UserResource($user)
            ]);

        } catch (Exception $e) {
            Log::error('Erro ao buscar usuário: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erro interno do servidor.',
                'errors' => config('app.debug') ? [$e->getMessage()] : []
            ], 500);
        }
    }    /**
     * Logout user and revoke current token
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Usuário não autenticado.'
                ], 401);
            }

            $logoutData = $this->authService->logout($user);

            return response()->json([
                'status' => 'success',
                'data' => new LogoutResource($logoutData)
            ]);

        } catch (Exception $e) {
            Log::error('Erro no logout: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erro interno do servidor.',
                'errors' => config('app.debug') ? [$e->getMessage()] : []
            ], 500);
        }
    }    
    
    /**
     * Logout from all devices (revoke all tokens)
     */
    public function logoutAll(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Usuário não autenticado.'
                ], 401);
            }

            $logoutData = $this->authService->logoutAll($user);

            return response()->json([
                'status' => 'success',
                'data' => new LogoutResource($logoutData)
            ]);        } catch (Exception $e) {
            Log::error('Erro no logout de todos os dispositivos: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erro interno do servidor.',
                'errors' => config('app.debug') ? [$e->getMessage()] : []
            ], 500);
        }
    }

    /**
     * Get active sessions for the authenticated user
     */
    public function getActiveSessions(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Usuário não autenticado.'
                ], 401);
            }

            $sessions = $this->tokenService->getActiveSessions($user);
            $currentTokenId = $request->attributes->get('current_token_id');

            // Mark current session
            foreach ($sessions as &$session) {
                if ($session['id'] === $currentTokenId) {
                    $session['is_current'] = true;
                    break;
                }
            }            return response()->json([
                'status' => 'success',
                'data' => [
                    'sessions' => SessionResource::collection($sessions),
                    'stats' => $this->tokenService->getTokenStats($user),
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Erro ao obter sessões ativas: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erro interno do servidor.',
                'errors' => config('app.debug') ? [$e->getMessage()] : []
            ], 500);
        }
    }    /**
     * Revoke specific session
     */
    public function revokeSession(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $tokenId = $request->input('token_id');
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Usuário não autenticado.'
                ], 401);
            }

            if (!$tokenId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Token ID é obrigatório.'
                ], 400);
            }

            $revoked = $this->tokenService->revokeToken($tokenId, $user);

            if ($revoked) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Sessão revogada com sucesso!'
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Sessão não encontrada ou já revogada.'
                ], 404);
            }

        } catch (Exception $e) {
            Log::error('Erro ao revogar sessão: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erro interno do servidor.',
                'errors' => config('app.debug') ? [$e->getMessage()] : []
            ], 500);
        }
    }

    /**
     * Refresh access token using refresh token from cookie or request
     */
    public function refreshToken(Request $request): JsonResponse
    {
        try {
            // Try to get refresh token from request body first, then from cookie
            $refreshToken = $request->input('refresh_token') ?? 
                           PassportCookieManagement::getRefreshTokenFromCookie($request);

            if (!$refreshToken) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Refresh token não encontrado.'
                ], 400);
            }

            // Validate refresh token
            if (!$this->refreshTokenService->isRefreshTokenValid($refreshToken)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Refresh token inválido ou expirado.'
                ], 401);
            }

            // Refresh the token
            $tokenData = $this->refreshTokenService->refreshAccessToken($refreshToken);

            if (!$tokenData) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Falha ao renovar o token.'
                ], 500);
            }

            // Get user for response
            $user = $this->refreshTokenService->getUserFromRefreshToken($refreshToken);

            return response()->json([
                'status' => 'success',
                'message' => 'Token renovado com sucesso!',
                'data' => [
                    'access_token' => $tokenData['access_token'],
                    'token_type' => $tokenData['token_type'],
                    'expires_in' => $tokenData['expires_in'],
                    'refresh_token' => $tokenData['refresh_token'] ?? null,
                    'user' => $user ? new UserResource($user) : null
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Erro ao renovar token: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erro interno do servidor.',
                'errors' => config('app.debug') ? [$e->getMessage()] : []
            ], 500);
        }
    }

    /**
     * Check authentication status using cookies
     */
    public function checkAuth(Request $request): JsonResponse
    {
        try {
            // Check if user is authenticated via token
            $user = $request->user();
            
            if ($user) {
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'authenticated' => true,
                        'user' => new UserResource($user),
                        'remember_me' => PassportCookieManagement::getRememberMeFromCookie($request)
                    ]
                ]);
            }

            // If not authenticated, check if we can refresh from cookie
            if (PassportCookieManagement::hasValidAuthCookies($request)) {
                $refreshToken = PassportCookieManagement::getRefreshTokenFromCookie($request);
                
                if ($this->refreshTokenService->isRefreshTokenValid($refreshToken)) {
                    return response()->json([
                        'status' => 'success',
                        'data' => [
                            'authenticated' => false,
                            'can_refresh' => true,
                            'remember_me' => PassportCookieManagement::getRememberMeFromCookie($request)
                        ]
                    ]);
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'authenticated' => false,
                    'can_refresh' => false,
                    'remember_me' => false
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Erro ao verificar autenticação: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erro interno do servidor.',
                'errors' => config('app.debug') ? [$e->getMessage()] : []
            ], 500);
        }
    }

    /**
     * Get refresh token statistics (admin only)
     */
    public function getRefreshTokenStats(): JsonResponse
    {
        try {
            // You might want to add authorization check here
            $stats = $this->refreshTokenService->getRefreshTokenStats();

            return response()->json([
                'status' => 'success',
                'data' => $stats
            ]);

        } catch (Exception $e) {
            Log::error('Erro ao obter estatísticas de refresh tokens: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erro interno do servidor.',
                'errors' => config('app.debug') ? [$e->getMessage()] : []
            ], 500);
        }
    }
}
