---
applyTo: '**/*.php'
---

> **🎯 PROMPT BACKEND:** Este arquivo contém instruções específicas para desenvolvimento PHP/Laravel.
> Para usar: `@copilot-backend-promp` + sua solicitação de código backend.

# Copilot ▸ Backend (PHP/Laravel)

> **🚀 OBJETIVO:** Gerar código backend robusto, performático e maintível seguindo as melhores práticas do ecossistema PHP/Laravel.

## 1 ▪ Convenções PHP/Laravel

### 1.1 Estrutura de Código
* **Declarar `declare(strict_types=1);`** em todo arquivo PHP
* **camelCase** para variáveis, métodos e propriedades: `$userName`, `getUserById()`
* **PascalCase** para classes: `UserService`, `CreateUserRequest`
* **snake_case** apenas para colunas de banco e migrations: `user_id`, `created_at`
* **SCREAMING_SNAKE_CASE** para constantes: `MAX_LOGIN_ATTEMPTS`, `DEFAULT_ROLE`
* **Models no singular**; tabelas no plural: `User` ↔ `users`
* **Namespace PSR-4** seguindo estrutura de pastas

### 1.2 Formatação & Code Quality
* **Laravel Pint** (PSR-12 + recomendações framework)
* **Indentação**: 4 espaços para PHP
* **Máximo 120 caracteres** por linha
* **PHPStan level 9** para análise estática
* **Rector** para modernização automática do código
* **PHP-CS-Fixer** integrado via Pint

### 1.3 Tipos e Documentação
* **Return types** obrigatórios em todos os métodos
* **Property types** para todas as propriedades de classe
* **Array shapes** documentados com PHPDoc quando necessário
* **Union types** (`string|int`) quando apropriado (PHP 8.0+)
* **Nullable types** (`?string`) explícitos

---

## 2 ▪ Arquitetura Laravel Moderna

### 2.1 Estrutura de Pastas
```
app/
├─ Actions/              # Single-purpose actions (alternative to Services)
├─ Console/             # Artisan commands
├─ DTO/                 # Data Transfer Objects
├─ Events/              # Domain events
├─ Exceptions/          # Custom exceptions
├─ Http/
│  ├─ Controllers/      # Thin controllers (orchestration only)
│  ├─ Middleware/       # Request/response middleware
│  ├─ Requests/         # Form request validation
│  └─ Resources/        # API response formatting
├─ Jobs/               # Queued jobs
├─ Listeners/          # Event listeners
├─ Mail/               # Mailable classes
├─ Models/             # Eloquent models
├─ Policies/           # Authorization policies
├─ Providers/          # Service providers
├─ Rules/              # Custom validation rules
└─ Services/           # Business logic services
```

### 2.2 Responsabilidades por Camada

| Camada                    | Responsabilidade                                                  | Tamanho Máximo |
| ------------------------- | ----------------------------------------------------------------- | -------------- |
| **Controller**            | Orquestra fluxo, valida Request, chama Service, devolve Resource | 100 LOC        |
| **Service/Action**        | Contém regra de negócio e transações                             | 200 LOC        |
| **Repository (opcional)** | Encapsula consultas Eloquent/Query Builder                       | 150 LOC        |
| **Resource**              | Formata saída JSON                                               | 50 LOC         |
| **Job/Event/Listener**    | Processamento assíncrono & side-effects                          | 100 LOC        |

### 2.3 Controllers Modernos
* **Apenas orquestração** - zero lógica de negócio
* **Injeção de dependência** via construtor (prefer) ou método
* **Métodos RESTful**: `index`, `show`, `store`, `update`, `destroy`
* **Autorização via Policies** - never in controllers
* **Single responsibility** - um controller por resource
* **Invokable controllers** para actions específicas

### 2.4 Services vs Actions
* **Services**: Para operações complexas com múltiplas responsabilidades
* **Actions**: Para operações simples e focadas (single responsibility)
* **Prefer Actions** para código mais testável e reutilizável
* **Constructor injection** para dependências
* **Final classes** para evitar herança desnecessária

---

## 3 ▪ Validação e DTOs Avançados

### 3.1 Form Requests com Tipos
```php
class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
    }

    public function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'max:150'],
            'email'   => ['required', 'email', 'unique:users,email'],
            'cpf'     => ['required', new CpfRule()],
            'role_id' => ['required', 'exists:roles,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Este email já está em uso.',
            'cpf.required' => 'CPF é obrigatório.',
        ];
    }

    public function toDTO(): CreateUserDTO
    {
        return new CreateUserDTO(
            name: $this->string('name'),
            email: $this->string('email'),
            cpf: $this->string('cpf'),
            roleId: $this->integer('role_id')
        );
    }
}
```

### 3.2 DTOs com Validação de Tipos
```php
readonly class CreateUserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $cpf,
        public int $roleId,
        public ?string $avatar = null,
    ) {
        // Validation in constructor if needed
        if (empty($this->name)) {
            throw new InvalidArgumentException('Name cannot be empty');
        }
    }

    public function toArray(): array
    {
        return [
            'name'    => $this->name,
            'email'   => $this->email,
            'cpf'     => $this->cpf,
            'role_id' => $this->roleId,
            'avatar'  => $this->avatar,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            cpf: $data['cpf'],
            roleId: $data['role_id'],
            avatar: $data['avatar'] ?? null,
        );
    }
}
```

### 3.3 Custom Validation Rules
```php
class CpfRule implements Rule
{
    public function passes($attribute, $value): bool
    {
        return $this->isValidCpf($value);
    }

    public function message(): string
    {
        return 'O CPF informado é inválido.';
    }

    private function isValidCpf(string $cpf): bool
    {
        // CPF validation logic
        return preg_match('/^\d{3}\.\d{3}\.\d{3}-\d{2}$/', $cpf);
    }
}
```

---

## 4 ▪ Respostas e Recursos Modernos

### 4.1 API Resources com Tipos
```php
class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'avatar_url' => $this->avatar ? Storage::url($this->avatar) : null,
            'role'       => RoleResource::make($this->whenLoaded('role')),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
```

### 4.2 Standardized Response Patterns
```php
trait ApiResponse
{
    protected function successResponse(
        mixed $data = null, 
        string $message = '', 
        int $status = 200
    ): JsonResponse {
        return response()->json([
            'status'  => 'success',
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    protected function errorResponse(
        string $message, 
        int $status = 400, 
        array $errors = []
    ): JsonResponse {
        return response()->json([
            'status'  => 'error',
            'message' => $message,
            'errors'  => $errors,
        ], $status);
    }

    protected function paginatedResponse(
        LengthAwarePaginator $paginator,
        string $resourceClass
    ): JsonResponse {
        return response()->json([
            'status' => 'success',
            'data'   => $resourceClass::collection($paginator->items()),
            'meta'   => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
                'from'         => $paginator->firstItem(),
                'to'           => $paginator->lastItem(),
            ],
        ]);
    }
}
```

### 4.3 Exception Handling
```php
class Handler extends ExceptionHandler
{
    public function render($request, Throwable $exception): Response
    {
        if ($request->expectsJson()) {
            return $this->handleApiException($request, $exception);
        }

        return parent::render($request, $exception);
    }

    private function handleApiException(Request $request, Throwable $exception): JsonResponse
    {
        if ($exception instanceof ValidationException) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed',
                'errors'  => $exception->errors(),
            ], 422);
        }

        if ($exception instanceof ModelNotFoundException) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Resource not found',
            ], 404);
        }

        // Log unexpected errors
        if (!$this->isHttpException($exception)) {
            Log::error('Unexpected error', [
                'exception' => $exception->getMessage(),
                'trace'     => $exception->getTraceAsString(),
                'request'   => $request->all(),
            ]);
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'Internal server error',
        ], 500);
    }
}
```

---

## 5 ▪ Performance & Database Optimization

### 5.1 Query Optimization
* **Eager loading** para evitar N+1: `User::with(['role', 'permissions'])`
* **Lazy eager loading** quando necessário: `$users->load('posts')`
* **Select specific columns**: `User::select(['id', 'name', 'email'])`
* **Chunking** para large datasets: `User::chunk(1000, fn($users) => ...)`
* **Cursor pagination** para performance: `User::cursorPaginate()`
* **Database indexes** para colunas filtradas frequentemente
* **Query caching** para consultas pesadas: `Cache::remember()`

### 5.2 Eloquent Best Practices
```php
// ✅ Good - Eager loading
$users = User::with('role')->get();

// ❌ Bad - N+1 query problem
$users = User::all();
foreach ($users as $user) {
    echo $user->role->name; // N+1 queries
}

// ✅ Good - Chunking large datasets
User::chunk(1000, function ($users) {
    foreach ($users as $user) {
        // Process user
    }
});

// ✅ Good - Raw queries for complex operations
DB::select('
    SELECT u.*, COUNT(p.id) as posts_count 
    FROM users u 
    LEFT JOIN posts p ON u.id = p.user_id 
    GROUP BY u.id
');
```

### 5.3 Caching Strategies
```php
class UserService
{
    public function getActiveUsers(): Collection
    {
        return Cache::remember(
            'users.active',
            now()->addHours(1),
            fn() => User::where('active', true)->get()
        );
    }

    public function clearUserCache(int $userId): void
    {
        Cache::forget("user.{$userId}");
        Cache::forget('users.active');
    }
}
```

---

## 6 ▪ Security & Authentication

### 6.1 Authentication & Authorization
```php
// Policies for authorization
class UserPolicy
{
    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, User $targetUser): bool
    {
        return $user->id === $targetUser->id || $user->hasRole('admin');
    }
}

// API Authentication with Sanctum
class LoginController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only(['email', 'password']);

        if (!Auth::attempt($credentials)) {
            return $this->errorResponse('Invalid credentials', 401);
        }

        $user = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;

        return $this->successResponse([
            'user' => UserResource::make($user),
            'token' => $token,
        ]);
    }
}
```

### 6.2 Input Sanitization & XSS Protection
```php
class CreatePostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title'   => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title'   => strip_tags($this->title),
            'content' => clean($this->content), // Using HTMLPurifier
        ]);
    }
}
```

### 6.3 Rate Limiting
```php
// In RouteServiceProvider
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});

// Apply to routes
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::apiResource('users', UserController::class);
});
```

---

## 7 ▪ Testing & Quality Assurance

### 7.1 Test Structure
```php
// Feature Test
class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_user(): void
    {
        $admin = User::factory()->admin()->create();
        $userData = User::factory()->make()->toArray();

        $response = $this->actingAs($admin)
            ->postJson('/api/users', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'status',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => $userData['email'],
        ]);
    }
}

// Unit Test for Service
class CreateUserServiceTest extends TestCase
{
    public function test_creates_user_successfully(): void
    {
        $dto = new CreateUserDTO(
            name: 'John Doe',
            email: 'john@example.com',
            cpf: '123.456.789-00',
            roleId: 1
        );

        $service = new CreateUserService();
        $user = $service->execute($dto);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->name);
    }
}
```

### 7.2 Test Coverage & CI/CD
* **Minimum 80% coverage** for critical paths
* **Pest** preferred over PHPUnit for better DX
* **Database testing** with transactions or RefreshDatabase
* **Mock external services** to avoid dependencies
* **Parallel testing** for faster CI execution

---

## 8 ▪ Events, Jobs & Queue Processing

### 8.1 Domain Events
```php
class UserCreated
{
    public function __construct(
        public readonly User $user
    ) {}
}

class SendWelcomeEmailListener
{
    public function handle(UserCreated $event): void
    {
        Mail::to($event->user->email)
            ->queue(new WelcomeEmail($event->user));
    }
}
```

### 8.2 Background Jobs
```php
class ProcessLargeDatasetJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly int $userId,
        private readonly array $data
    ) {}

    public function handle(): void
    {
        // Process large dataset
        foreach (array_chunk($this->data, 1000) as $chunk) {
            // Process chunk
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Job failed', [
            'job' => self::class,
            'user_id' => $this->userId,
            'exception' => $exception->getMessage(),
        ]);
    }
}
```

---

## 9 ▪ Exemplos Práticos Completos

### 9.1 Controller RESTful Moderno
```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Services\CreateUserService;
use App\Services\UpdateUserService;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly CreateUserService $createUserService,
        private readonly UpdateUserService $updateUserService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $users = User::with('role')
            ->when($request->search, fn($query, $search) => 
                $query->where('name', 'like', "%{$search}%")
            )
            ->paginate($request->per_page ?? 15);

        return $this->paginatedResponse($users, UserResource::class);
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->createUserService->execute($request->toDTO());

        return $this->successResponse(
            UserResource::make($user),
            'User created successfully',
            201
        );
    }

    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        return $this->successResponse(
            UserResource::make($user->load('role'))
        );
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $updatedUser = $this->updateUserService->execute($user, $request->toDTO());

        return $this->successResponse(
            UserResource::make($updatedUser),
            'User updated successfully'
        );
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        return $this->successResponse(null, 'User deleted successfully');
    }
}
```

### 9.2 Service com Transações e Events
```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\DTO\CreateUserDTO;
use App\Events\UserCreated;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final readonly class CreateUserService
{
    public function execute(CreateUserDTO $dto): User
    {
        return DB::transaction(function () use ($dto): User {
            $user = User::create([
                'name'     => $dto->name,
                'email'    => $dto->email,
                'password' => Hash::make($dto->password),
                'cpf'      => $dto->cpf,
                'role_id'  => $dto->roleId,
            ]);

            // Dispatch domain event
            event(new UserCreated($user));

            return $user;
        });
    }
}
```

### 9.3 Repository Pattern (Opcional)
```php
<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

final readonly class UserRepository
{
    public function findActiveUsers(): Collection
    {
        return User::where('active', true)
            ->with('role')
            ->get();
    }

    public function findWithFilters(array $filters): LengthAwarePaginator
    {
        return User::query()
            ->when($filters['search'] ?? null, fn($query, $search) =>
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
            )
            ->when($filters['role_id'] ?? null, fn($query, $roleId) =>
                $query->where('role_id', $roleId)
            )
            ->with('role')
            ->paginate($filters['per_page'] ?? 15);
    }

    public function create(array $data): User
    {
        return User::create($data);
    }
}
```

### 9.4 Migration Otimizada
```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('email')->unique();
            $table->string('cpf', 14)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->boolean('active')->default(true);
            $table->rememberToken();
            $table->timestamps();

            // Indexes for performance
            $table->index(['active', 'created_at']);
            $table->index('email');
            $table->index('cpf');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
```

### 9.5 Model com Relationships e Scopes
```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'name',
        'email',
        'cpf',
        'password',
        'role_id',
        'active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'active' => 'boolean',
    ];

    // Relationships
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function scopeWithRole(Builder $query, string $roleName): Builder
    {
        return $query->whereHas('role', fn($q) => $q->where('name', $roleName));
    }

    // Accessors & Mutators
    public function getIsAdminAttribute(): bool
    {
        return $this->role?->name === 'admin';
    }

    // Methods
    public function hasRole(string $roleName): bool
    {
        return $this->role?->name === $roleName;
    }
}
```
