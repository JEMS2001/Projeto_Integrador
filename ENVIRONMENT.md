# Configuração do Ambiente - Projeto Integrador

Este documento descreve a configuração do ambiente de desenvolvimento e produção para o Projeto Integrador, um sistema completo de gestão empresarial com Laravel (backend) e Next.js (frontend) executando em Docker.

## Estrutura do Projeto

```
Projeto_Integrador/
├── backend/                    # API Laravel 11 (PHP 8.4)
├── frontend/                   # Interface Next.js 15 (TypeScript)
├── Projeto-Integrador/         # Sistema Legacy PHP (referência)
├── .taskmaster/               # Gestão de tarefas do projeto
├── .github/instructions/      # Guidelines EnterScience
├── docker-compose.yml         # Orquestração Laravel Sail
└── .env.project.example       # Template de variáveis
```

## Arquitetura Atual (Laravel Sail)

### 1. Laravel Backend (`laravel.test`)
- **Container**: `laravel.test`
- **Porta**: 8000 (HTTP) + 5173 (Vite dev server)
- **Framework**: Laravel 11 com Sail
- **PHP**: 8.4 (runtime sail-8.4/app)
- **Ambiente**: `backend/.env.development`

### 2. Next.js Frontend (`frontend`)
- **Container**: `projeto_frontend`
- **Porta**: 3000
- **Framework**: Next.js 15 com TypeScript
- **Runtime**: Bun
- **Ambiente**: Variáveis definidas no docker-compose.yml

### 3. MySQL Database (`mysql`)
- **Container**: `mysql` (mysql-server:8.0)
- **Porta**: 3306
- **Database**: `projeto_integrador_dev`
- **Usuário**: `app_user` / `root`
- **Senha**: `password`
- **Healthcheck**: mysqladmin ping

## Configuração de Variáveis (Laravel Sail)
### Arquivo Principal: `.env.project.example`

Todas as variáveis de ambiente estão centralizadas no arquivo `.env.project.example`:

```bash
# =============================================================================
# Projeto Integrador - Environment Configuration
# =============================================================================

# Application Configuration
APP_NAME="Projeto Integrador"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=projeto_integrador_dev
DB_USERNAME=root
DB_PASSWORD=rootpassword

# Docker Environment (Laravel Sail)
WWWGROUP=1000
WWWUSER=1000
APP_PORT=8000
FORWARD_DB_PORT=3306
VITE_PORT=5173

# Laravel Specific
APP_KEY=base64:your-generated-app-key-here
APP_LOCALE=pt_BR
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=pt_BR

# Session Configuration
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false

# Cache Configuration
CACHE_STORE=database

# Queue Configuration
QUEUE_CONNECTION=database

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@projetointegrador.com
MAIL_FROM_NAME="${APP_NAME}"

# Authentication & Security
JWT_SECRET=your-jwt-secret-key
AUTH_GUARD=web
AUTH_PASSWORD_BROKER=users

# Frontend Configuration
NEXT_PUBLIC_API_URL=http://localhost:8000
NEXT_PUBLIC_APP_NAME="Projeto Integrador"
NODE_ENV=development

# Company Management
DEFAULT_COMPANY_ROLE=employee
MAX_MEMBERS_PER_COMPANY=100

# Task Management
DEFAULT_TASK_STATUS=pending
TASK_STATUSES=pending,in-progress,review,done,cancelled

# Notification Settings
NOTIFICATION_CHANNELS=email,database
EMAIL_NOTIFICATIONS_ENABLED=true
```

### Configuração por Serviço

#### Laravel Backend (via docker-compose.yml)
```yaml
environment:
  WWWUSER: '${WWWUSER:-1000}'
  LARAVEL_SAIL: 1
  XDEBUG_MODE: '${SAIL_XDEBUG_MODE:-off}'
  XDEBUG_CONFIG: '${SAIL_XDEBUG_CONFIG:-client_host=host.docker.internal}'
  IGNITION_LOCAL_SITES_PATH: '${PWD}/backend'
```

#### MySQL Database (via docker-compose.yml)
```yaml
environment:
  MYSQL_ROOT_PASSWORD: '${DB_PASSWORD:-password}'
  MYSQL_ROOT_HOST: '%'
  MYSQL_DATABASE: '${DB_DATABASE:-projeto_integrador_dev}'
  MYSQL_USER: '${DB_USERNAME:-app_user}'
  MYSQL_PASSWORD: '${DB_PASSWORD:-password}'
  MYSQL_ALLOW_EMPTY_PASSWORD: 1
```

#### Next.js Frontend (via docker-compose.yml)
```yaml
environment:
  NODE_ENV: '${NODE_ENV:-development}'
  NEXT_PUBLIC_API_URL: '${NEXT_PUBLIC_API_URL:-http://localhost:8000}'
  NEXT_PUBLIC_APP_NAME: '${NEXT_PUBLIC_APP_NAME:-Projeto Integrador}'
```

## Comandos para Desenvolvimento

### Setup Inicial
```bash
# 1. Copiar arquivo de exemplo
cp .env.project.example .env

# 2. Iniciar todos os serviços
docker-compose up -d

# 3. Instalar dependências Laravel
docker-compose exec laravel.test composer install

# 4. Gerar chave da aplicação
docker-compose exec laravel.test php artisan key:generate

# 5. Executar migrações
docker-compose exec laravel.test php artisan migrate

# 6. Instalar dependências Frontend
docker-compose exec frontend bun install
```

### Comandos Cotidianos
```bash
# Iniciar serviços
docker-compose up -d

# Parar serviços
docker-compose down

# Ver logs
docker-compose logs -f laravel.test
docker-compose logs -f frontend
docker-compose logs -f mysql

# Acessar containers
docker-compose exec laravel.test bash
docker-compose exec frontend bash

# Validar configuração
docker-compose config
```

### Comandos Laravel Sail
```bash
# Artisan commands
docker-compose exec laravel.test php artisan migrate
docker-compose exec laravel.test php artisan db:seed
docker-compose exec laravel.test php artisan cache:clear

# Composer
docker-compose exec laravel.test composer install
docker-compose exec laravel.test composer update

# Testes
docker-compose exec laravel.test php artisan test
```

## URLs de Acesso

- **Frontend Next.js**: http://localhost:3000
- **Backend Laravel**: http://localhost:8000
- **Vite Dev Server**: http://localhost:5173
- **MySQL**: localhost:3306
- **Sistema Legacy**: `Projeto-Integrador/` (referência)

## Configuração Seguindo EnterScience Guidelines

### Estrutura de Pastas (Backend Laravel)
```
backend/
├── app/
│   ├── DTOs/              # Data Transfer Objects
│   ├── Http/
│   │   ├── Controllers/   # Controllers com responsabilidade única
│   │   ├── Requests/      # Form Requests para validação
│   │   └── Resources/     # API Resources para formatação
│   ├── Models/            # Eloquent Models
│   └── Services/          # Business Logic Services
├── database/
│   ├── migrations/        # Database migrations
│   └── seeders/          # Database seeders
└── tests/
    ├── Feature/          # Integration tests
    └── Unit/             # Unit tests
```

### Padrões de Código
- **DTOs**: Para transferência de dados estruturada
- **Form Requests**: Para validação server-side
- **API Resources**: Para formatação de respostas
- **Services**: Para lógica de negócio
- **Testes**: Unit e Integration tests obrigatórios

### Autenticação (Laravel Passport)
```php
# Configuração no backend/.env
# OAuth2 configuration for Laravel Passport
SESSION_DRIVER=database
```

## Configuração de Produção

Para produção, crie um arquivo `.env` baseado no `.env.project.example` com as seguintes alterações:

### Variáveis Críticas para Produção
```bash
# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Security
APP_KEY=base64:GENERATE_NEW_32_CHAR_KEY
JWT_SECRET=GENERATE_SECURE_JWT_SECRET

# Database
DB_HOST=your-production-mysql-host
DB_DATABASE=projeto_integrador_prod
DB_USERNAME=secure_username
DB_PASSWORD=secure_password

# Mail (configurar SMTP real)
MAIL_HOST=your-smtp-host
MAIL_USERNAME=your-smtp-user
MAIL_PASSWORD=your-smtp-password

# Frontend
NEXT_PUBLIC_API_URL=https://api.your-domain.com
NODE_ENV=production
```

### Deploy com Docker
```bash
# 1. Configurar variáveis de produção
cp .env.project.example .env.production

# 2. Build para produção
docker-compose -f docker-compose.yml -f docker-compose.prod.yml build

# 3. Deploy
docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

## Troubleshooting

### Problemas Comuns

1. **Erro de permissão no Laravel (Windows)**:
   ```powershell
   # Via PowerShell
   docker-compose exec laravel.test chmod -R 775 storage bootstrap/cache
   ```

2. **Conflito de portas**:
   - Verificar se as portas 3000, 8000, 5173, 3306 estão livres
   ```powershell
   netstat -an | Select-String ":3000|:8000|:5173|:3306"
   ```

3. **Erro de conexão com banco**:
   - Aguardar MySQL inicializar (use healthcheck)
   ```bash
   docker-compose logs mysql
   ```

4. **Cache do Docker**:
   ```powershell
   docker-compose down
   docker-compose build --no-cache
   docker-compose up -d
   ```

5. **Problemas com Sail no Windows**:
   ```powershell
   # Verificar permissões WSL2
   wsl --list --verbose
   
   # Rebuildar se necessário
   docker-compose down --volumes
   docker-compose up --build -d
   ```

### Debugging

#### Verificar Status dos Serviços
```powershell
# Status dos containers
docker-compose ps

# Logs em tempo real
docker-compose logs -f --tail=50

# Verificar saúde do MySQL
docker-compose exec mysql mysqladmin ping -p
```

#### Testes de Conectividade
```bash
# Testar API Laravel
curl http://localhost:8000

# Testar Frontend
curl http://localhost:3000

# Testar banco de dados
docker-compose exec mysql mysql -u root -p -e "SHOW DATABASES;"
```

## Volumes e Persistência

### Volumes Docker
- `sail-mysql`: Dados persistentes do MySQL
- `./backend`: Código Laravel (bind mount)
- `./frontend`: Código Next.js (bind mount)
- `/app/node_modules`: Node modules do frontend (volume anônimo)

### Backup de Dados
```powershell
# Backup do banco MySQL
docker-compose exec mysql mysqldump -u root -p projeto_integrador_dev > backup.sql

# Restaurar backup
Get-Content backup.sql | docker-compose exec -T mysql mysql -u root -p projeto_integrador_dev
```

## Rede Docker

Todos os serviços estão na rede `sail` (bridge) e podem se comunicar usando os nomes dos containers:
- `laravel.test` → Backend Laravel
- `mysql` → Banco de dados
- `frontend` → Frontend Next.js

## Próximos Passos

### Implementação das Tarefas EnterScience
1. **Autenticação (Task #6)**: Implementar Laravel Passport OAuth2
2. **DTOs e Form Requests**: Estruturar dados seguindo guidelines
3. **API Resources**: Formatação padronizada de respostas
4. **Testes**: Unit e Integration tests
5. **Migração do Sistema Legacy**: Transferir funcionalidades

### Referências
- **Guidelines**: `.github/instructions/`
- **Tasks**: `.taskmaster/tasks/tasks.json`
- **Sistema Legacy**: `Projeto-Integrador/` (para referência funcional)
- **Laravel Sail**: https://laravel.com/docs/sail
- **Next.js**: https://nextjs.org/docs

---

**Última atualização**: 07/06/2025 - Documentação completa da configuração de ambiente seguindo as EnterScience Guidelines.
