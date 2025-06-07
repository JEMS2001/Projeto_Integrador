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
use App\Http\Resources\Auth\UserResource;
use App\Http\Resources\EmpresaResource;
use App\Http\Resources\MembroResource;
use App\Models\Empresa;
use App\Models\Membro;
use App\Services\Auth\AuthService;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
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
    }    /**
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
            ]);

        } catch (Exception $e) {
            Log::error('Erro no logout de todos os dispositivos: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erro interno do servidor.',
                'errors' => config('app.debug') ? [$e->getMessage()] : []
            ], 500);
        }
    }
}
