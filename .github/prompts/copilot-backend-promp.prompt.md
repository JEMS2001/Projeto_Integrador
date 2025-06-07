---
mode: 'agent'
tools: ['githubRepo','codebase']
description: 'Prompt para auxiliar na geração de código backend robusto em PHP/Laravel, com foco em performance, segurança e manutenibilidade.'
---
name: 'Copilot Backend Prompt'

> **📋 COMO USAR:** Digite `@copilot-backend-promp` seguido da sua solicitação para desenvolvimento PHP/Laravel.
> **🎯 APLICA-SE A:** Controllers, Services, Models, Migrations, Requests, Jobs, Policies, Tests.
> **🚀 OBJETIVO:** Código backend type-safe, performático e seguro seguindo Laravel best practices.

## 1. **Arquitetura & Responsabilidades**
- **Controllers**: Orquestração apenas → Validate Request, call Service, return Resource
- **Services/Actions**: Business logic, transactions, complex operations
- **Repositories**: Query abstraction (optional, prefer Eloquent when simple)
- **Policies**: Authorization logic, never in controllers
- **Jobs**: Background processing, async operations
- **Events/Listeners**: Domain events, side effects

## 2. **Code Quality & Standards**
- **Strict Types**: `declare(strict_types=1);` em todos os arquivos
- **Laravel Pint**: PSR-12 + framework recommendations
- **Return Types**: Obrigatórios em todos os métodos
- **Property Types**: Para todas as propriedades de classe
- **PHPStan Level 9**: Análise estática rigorosa
- **Nomenclatura**: camelCase (código), snake_case (DB), PascalCase (classes)

3. **Validation & DTOs**
- **Form Requests**: Validação centralizada com authorize() e rules()
- **Custom Rules**: Para validações complexas reutilizáveis
- **DTOs readonly**: Para transferência type-safe de dados
- **Constructor validation**: Para business rules nos DTOs

4. **Database & Performance**
- **Eager Loading**: Evitar N+1 queries com with()
- **Query Optimization**: select(), chunk(), indexes apropriados
- **Caching**: Cache::remember() para queries pesadas
- **Transactions**: DB::transaction() para operações críticas
- **Performance**: Documentar complexidade > O(n log n)

5. **Security & Authentication**
- **Laravel Passport**: Para OAuth2 API authentication
- **Policies**: Authorization logic fora dos controllers
- **Input Sanitization**: strip_tags(), HTMLPurifier para conteúdo
- **Rate Limiting**: Throttling para endpoints críticos
- **Validation**: Sempre validar entrada do usuário

6. **API Responses & Resources**
- **Standardized Responses**: ApiResponse trait com success/error patterns
- **API Resources**: Formatação consistente de JSON
- **Exception Handling**: Handler centralizado para diferentes tipos de erro
- **HTTP Status Codes**: Usar códigos apropriados (200, 201, 422, 404, 500)

7. **Testing & Quality**
- **Pest**: Preferido sobre PHPUnit para melhor DX
- **Feature Tests**: Teste endpoints completos
- **Unit Tests**: Teste Services/Actions isoladamente
- **Database**: RefreshDatabase ou transactions
- **Coverage**: Mínimo 80% para código crítico

8. **Background Processing**
- **Jobs**: Para operações assíncronas, implementar ShouldQueue
- **Events**: Para domain events e side effects
- **Queues**: Redis/Database queues para produção
- **Failed Jobs**: Proper error handling e logging

### 🎯 Como Agir
- **Context Detection**: Verificar Models/Services existentes antes de criar novos
- **No Duplication**: Reutilizar lógica existente, não duplicar
- **Incremental**: Melhorias graduais vs reescrita completa
- **Database First**: Sempre incluir migrations para mudanças de schema
- **Security First**: Validação, authorization e sanitization por padrão

### 📋 Detection Patterns
```php
// Detectar padrão existente
if (Service exists) -> extend/use existing
if (Policy exists) -> follow same authorization pattern
if (Resource exists) -> follow same response structure
if (Migration exists) -> follow same naming convention
```

### ⚡ Priority Order
1. **Security** - Validation, authorization, sanitization
2. **Performance** - Query optimization, caching, eager loading
3. **Type Safety** - Strict types, return types, DTOs
4. **Testability** - Clean architecture, dependency injection
5. **Maintainability** - Single responsibility, clear naming

### 🔧 Development Workflow
- **Analyze first**: Understand existing patterns and architecture
- **Follow conventions**: Maintain consistency with codebase
- **Test coverage**: Write tests for new functionality
- **Documentation**: Update relevant docs and examples